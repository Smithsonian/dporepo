@ECHO OFF

@REM iuvMops.bat
@REM Creates a web-ready model from a RealityCapture .obj file

rem @SET IUV_KEYFILE=C:\JobBoxProcess\settings\iuv_keyfile.xml
@SET jobDirectory=C:\JobBoxProcess\

@REM find the first folder that:
@REM   - does have an imageMagick finish file (_finish_im.txt)
@REM   - does have a "processed" subdirectory
@REM   - does have at least one image (.tif or .jpg) file within the "processed" subdirectory
for /f "tokens=*" %%d in ('dir %jobDirectory% /ad /b /o-d') do (
    echo full folder path: %jobDirectory%%%d\
	IF EXIST %jobDirectory%%%d\_finish_rc.txt (
		IF NOT EXIST %jobDirectory%%%d\_start_iuv.txt (
			IF EXIST %jobDirectory%%%d\processed (
				IF EXIST %jobDirectory%%%d\processed\*.obj (
					rem ECHO "GOT HERE"
					@SET processDirectory=%jobDirectory%%%d\
					goto :step2
				)
			)
		)
		rem IF EXIST %jobDirectory%%%d\processed ( ECHO "processed")
		rem IF EXIST %jobDirectory%%%d\_finish_iuv.txt ( ECHO "started")
	)
	IF NOT EXIST %jobDirectory%%%d\_finish_rc.txt ( ECHO "master model (.obj) not ready" )
)

:step2
IF DEFINED processDirectory (
	IF NOT EXIST %jobDirectory%%%d\_start_iuv.txt (
		@REM echo out the processing directory name
		ECHO processDir=%processDirectory%
		@REM run the master 3D model generation via RealityCapture
		ECHO starting > %processDirectory%_start_iuv.txt
		mops --read_config C:\JobBoxProcess\settings\iuv_config.json -i %processDirectory%processed\mesh.obj -w %processDirectory%processed\webready
		ECHO done > %processDirectory%_finish_iuv.txt
	)
)