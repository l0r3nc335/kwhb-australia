<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateModuleCommand extends Command
{
    protected string $moduleName;
    protected string $modulePath;
    protected string $commandPath;
    protected string $version;
    protected string|null $fileName;
    protected string $fileType;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make {name} {--ver=v1} {--type=init} {--name=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate module directories and configurations';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->moduleName = $name = ucfirst(strtolower($this->argument('name')));
        $this->version = $version = $this->option('ver');
        $this->fileType = $this->option('type');
        $this->fileName = $this->option('name');
        $basePath = "app/Core";
        if (!is_dir($basePath)) {
            File::makeDirectory($basePath, 0755, true, true);
        }
        $versionPath = $basePath . '/' . $version;
        if (!is_dir($versionPath)) {
            File::makeDirectory($versionPath, 0755, true, true);
        }
        $this->modulePath = $modulePath = $versionPath . '/' . $name;
        $this->commandPath = str_replace("app/","", $modulePath);
        if (in_array($this->fileType, ['controller', 'model', 'request']) && empty($this->fileName)) {
            $this->error('Missing [--name] option');
            return;
        }
        switch ($this->fileType) {
            case 'controller':
                $this->makeController($this->fileName);
                break;
            case 'model':
                $this->makeModel($this->fileName);
                break;
            case 'request':
                $this->makeRequest($this->fileName);
                break;
            case 'rule':
                $this->makeRule($this->fileName);
                break;
            case 'job':
                $this->makeJob($this->fileName);
                break;
            default:
                if (is_dir($modulePath)) {
                    $this->error("Module with name {$name} already exist.");
                    return;
                }
                $this->generateModuleDirectories();
        }
        $this->info("Done.");
    }
    private function generateModuleDirectories()
    {
        $moduleName = $this->moduleName;
        $modulePath = $this->modulePath;
        $commandPath = $this->commandPath;
        // Http directories
        $httpPath = $modulePath . '/Http';
        File::makeDirectory($httpPath, 0755, true, true);
        $requestPath = $modulePath . '/Http/Requests';
        File::makeDirectory($requestPath, 0755, true, true);
        $middlewarePath = $modulePath . '/Http/Middleware';
        File::makeDirectory($middlewarePath, 0755, true, true);
        // Repositories directory
        $repositoryPath = $modulePath . '/Repositories';
        File::makeDirectory($repositoryPath, 0755, true, true);
        $repositoryClassPath = "App\\{$commandPath}/Repositories/{$moduleName}Repository";
        $this->call("make:class", ['name' => $repositoryClassPath]);
        // Routes directory
        $routesPath = $modulePath . '/routes';
        File::makeDirectory($routesPath, 0755, true, true);
        File::put("{$routesPath}/api.php", "<?php");
        File::put("{$routesPath}/web.php", "<?php");
        $this->configRouteServiceProviderForModuleRoutes($moduleName);
        // Generate Controller
        $this->makeController("{$moduleName}Controller");
        // Generate Model
        $this->makeModel($this->moduleName);
    }
    private function makeController(string $name): void
    {
        $controllerPath = "App\\{$this->commandPath}/Http/Controllers/{$name}";
        $this->call("make:controller", ['name' => $controllerPath]);
    }
    private function makeModel(string $name): void
    {
        $modelName = "App\\{$this->commandPath}/Models/{$name}";
        $this->call("make:model", ['name' => $modelName]);
    }
    private function makeRequest(string $name)
    {
        $requestPath = "App\\{$this->commandPath}/Http/Requests/{$name}";
        $this->call("make:request", ['name' => $requestPath]);
    }
    private function makeRule(string $name)
    {
        $ruleName = "App\\{$this->commandPath}/Rules/{$name}";
        $this->call("make:rule", ['name' => $ruleName]);
    }

    private function makeJob(string $name)
    {
        $ruleName = "App\\{$this->commandPath}/Jobs/{$name}";
        $this->call("make:job", ['name' => $ruleName]);
    }

    private function makeSeeder(string $name)
    {
        $seederName = "App\\{$this->commandPath}/database/seeders/{$name}";
        $this->call("make:seeder", ['name' => $seederName]);
    }
    private function configRouteServiceProviderForModuleRoutes(string $moduleName)
    {
        $classFilePath = app_path('Providers/RouteServiceProvider.php');
        if (file_exists($classFilePath)) {
            // Create a ReflectionClass instance
            $reflectionClass = new \ReflectionClass("\App\Providers\RouteServiceProvider");
            // Specify the name of the method you want to modify
            $methodName = 'boot';
            if ($reflectionClass->hasMethod($methodName)) {
                // Get the ReflectionMethod object for the specified method
                $reflectionMethod = $reflectionClass->getMethod($methodName);
                // Read the contents of the method from the file
                $methodContents = file($classFilePath);
                // Get the starting line number of the method
                $endLine = $reflectionMethod->getEndLine();
                // Get the lines of the method
                $methodLines = array_slice($methodContents, $reflectionMethod->getStartLine() - 1, $endLine - $reflectionMethod->getStartLine()  + 1);
                // Find the line number where you want to insert the new code
                $insertLine = $endLine - 1; // Inserting code after the second line of the method
                // Insert your new code at the desired line
                $lowerModuleName = strtolower($moduleName);
                $pluralModuleName = Str::plural($lowerModuleName);
                $newCode = "\n\t\t\t // {$moduleName} Module \n\t\t\tRoute::middleware('api')\n\t\t\t\t->prefix('api/v1/{$pluralModuleName}')\n\t\t\t\t->group(base_path('app/Core/v1/{$moduleName}/routes/api.php'));";
                $newCode .= "\n\t\t\tRoute::middleware('web')\n\t\t\t\t->group(base_path('app/Core/v1/{$moduleName}/routes/web.php'));\n";
                array_splice($methodLines, $insertLine - $reflectionMethod->getStartLine(), 0, $newCode);
                // Join the modified lines back into a string
                $modifiedMethodContents = implode('', $methodLines);
                // Replace the method contents in the file with the modified contents
                $fileContents = file_get_contents($classFilePath);
                $fileContents = str_replace(implode('', array_slice($methodContents,  $reflectionMethod->getStartLine() - 1, $reflectionMethod->getEndLine() - $reflectionMethod->getStartLine() + 1)), $modifiedMethodContents, $fileContents);
                file_put_contents($classFilePath, $fileContents);
            }
        }
    }
}
