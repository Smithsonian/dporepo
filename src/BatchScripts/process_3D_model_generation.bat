@REM process_3D_model_generation.bat
@REM Process 3D model generation

@REM Scan the directories of the JobBox by date descending (look in the newest folder first)
@SET jobDirectory=C:\JobBoxProcess\
@SET processDirectory=

@REM find the first folder that:
@REM   - does have _ready.txt file
@REM   - does not have an imageMagick start file
for /f "tokens=*" %%d in ('dir %jobDirectory% /ad /b /o-d') do (
	echo full folder path: %jobDirectory%%%d\
	IF EXIST %jobDirectory%%%d\_ready.txt (
		IF NOT EXIST %jobDirectory%%%d\_start_im.txt (
			rem SET processDirectory=%jobDirectory%%%d\
			goto :step2
		)
	)
)

:step2
CALL C:\inetpub\wwwroot\3dreponew\site\library\batch_scripts\imageMagick.bat
CALL C:\inetpub\wwwroot\3dreponew\site\library\batch_scripts\realityCapture.bat
CALL C:\inetpub\wwwroot\3dreponew\site\library\batch_scripts\iuvMops.bat
exit