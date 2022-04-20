if (!(Test-Path ".\artisan" -PathType Leaf)) {
  echo "ERROR: Artisan not found in this directory"
  exit
}

$input = Read-Host -Prompt "Enter controller names separated by commas"

if (!$input) {
  echo "ERROR: No controller names entered"
  exit
}

echo "Enter switches to create additional classes (like -msfc)"
$switch = Read-Host -Prompt "Enter the desired switches"

if (!$switch) {
  echo "WARNING: No switch selected"
} else {
  if ($switch -notcontains "-") {
    $switch = "--" + $switch
  }
}

$input = $input -replace '\s',''
$switch = $switch -replace '\s',''
$controllers = $input.Split(",")

foreach ($controller in $controllers) {
  echo "Creating controller $controller"
  php artisan make:controller $controller $switch
}