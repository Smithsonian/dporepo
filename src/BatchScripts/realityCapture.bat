@REM realityCapture.bat
@REM Scans JobBoxProcess for scans which have been clipped via ImageMagick, and processes the master 3D model via RealityCapture

@REM Scan the directories of the JobBox by date descending (look in the newest folder first)
@SET PATH=%PATH%;C:\Program Files\Capturing Reality\RealityCapture\
@SET jobDirectory=C:\JobBoxProcess\
@SET processDirectory=

@REM find the first folder that:
@REM   - does have an imageMagick finish file (_finish_im.txt)
@REM   - does have a "clipped" subdirectory
@REM   - does have at least one image (.tif or .jpg) file within the "clipped" subdirectory
for /f "tokens=*" %%d in ('dir %jobDirectory% /ad /b /o-d') do (
echo full folder path: %jobDirectory%%%d\
	IF EXIST %jobDirectory%%%d\_finish_im.txt (
		IF EXIST %jobDirectory%%%d\clipped (
			IF EXIST %jobDirectory%%%d\clipped\*.jpg (
				SET processDirectory=%jobDirectory%%%d\
				goto :step2
			)
		)
		IF EXIST %jobDirectory%%%d\clipped ( ECHO "clipped")
		IF EXIST %jobDirectory%%%d\_start_rc.txt ( ECHO "started")
	)
	IF NOT EXIST %jobDirectory%%%d\_ready.txt ( ECHO "not ready" )
)

:step2
ECHO processDir=%processDirectory%
@REM run the master 3D model generation via RealityCapture
ECHO starting > %processDirectory%_start_rc.txt
mkdir %processDirectory%processed

RealityCapture.exe -addFolder %processDirectory%clipped\ -align -setReconstructionRegionAuto -selectMaximalComponent -mvs -calculateVertexColors -calculateTexture -exportModel "Model 1" %processDirectory%processed\mesh.obj C:\JobBoxProcess\settings\params.xml -save %processDirectory%processed\mesh -quit

ECHO done > %processDirectory%_finish_rc.txt