@REM imageMagick.bat
@REM Scans the job box looking for an imageMagick job to process.

@REM Scan the directories of the JobBox by date descending (look in the newest folder first)
@SET jobDirectory=C:\JobBoxProcess\
@SET processDirectory=
@SET imageType=

@REM find the first folder that:
@REM   - does not have an imageMagick start file
@REM   - does not have a "clipped" subdirectory
@REM   - does have at least one image (.tif or .jpg) file
for /f "tokens=*" %%d in ('dir %jobDirectory% /ad /b /o-d') do (
echo full folder path: %jobDirectory%%%d\
	IF EXIST %jobDirectory%%%d\_ready.txt (
		IF NOT EXIST %jobDirectory%%%d\_start_im.txt (
			IF NOT EXIST %jobDirectory%%%d\clipped (
				IF EXIST %jobDirectory%%%d\*.jpg (
					SET processDirectory=%jobDirectory%%%d\
    					SET imageType=jpg
					goto :step2
				)
				IF EXIST %jobDirectory%%%d\*.tif (
					SET processDirectory=%jobDirectory%%%d\
    					SET imageType=tif
					goto :step2
				)
				ECHO "no images"
			)
			IF EXIST %jobDirectory%%%d\clipped ( ECHO "clipped")
		)
		IF EXIST %jobDirectory%%%d\_start_im.txt ( ECHO "started")
	)
	IF NOT EXIST %jobDirectory%%%d\_ready.txt ( ECHO "not ready" )
)

:step2
ECHO processDir=%processDirectory%
ECHO imageType=%imageType%
@REM for each image file in the folder, generate a clipped image file and drop in the "clipped" folder.

ECHO starting > %processDirectory%_start_im.txt
mkdir %processDirectory%clipped

for /f "tokens=*" %%f in ('dir %processDirectory%*.%imageType% /a-d /b') do (
	ECHO %%f
	magick -quiet %processDirectory%%%f -black-threshold 10%% -quality 100 %processDirectory%clipped\%%f.jpg
)
ECHO done > %processDirectory%_finish_im.txt