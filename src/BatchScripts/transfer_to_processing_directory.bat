robocopy c:\JobBox c:\JobBoxProcess /E /MIR /XX
del /q c:\JobBox\*.*
for /d %i in (c:\JobBox\*.*) do @rmdir /s /q "%i"