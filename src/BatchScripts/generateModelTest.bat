@REM generateModel.bat
@REM Checks the database to find the next 'web-multi' workflow step which hasn't been executed.

@REM Add the directory containing php.exe to the path
PATH=%PATH%;C:\PHP\

@REM Change into the root of the Symfony application's directory.
CD C:\xampp\htdocs\dporepo_test

@REM Execute php bin/console app:model-generate.
php bin/console app:model-generate