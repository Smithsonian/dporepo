@REM bagitValidate.bat
@REM Checks the database to find the next job which hasn't had a BagIt validation performed against it.
@REM If there is no bag, one is generated with the files contained within the job's directory of uploaded assets.

@REM Add the directory containing php.exe to the path
PATH=%PATH%;C:\PHP\

@REM Change into the root of the Symfony application's directory.
CD C:\inetpub\wwwroot\3drepo_test

@REM Execute bagit-validate.
php bin/console app:bagit-validate