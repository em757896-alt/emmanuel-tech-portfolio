@echo off
echo Starting proxy...
start /B "" node "C:\Users\Emmanuel\AppData\Local\Temp\opencode\proxy.js"
timeout /t 3 /nobreak >nul
echo Running Gradle build...
set JAVA_HOME=C:\Program Files\Android\Android Studio\jbr
call gradlew.bat assembleDebug --no-daemon
echo Killing proxy...
taskkill /f /im node.exe >nul 2>&1
echo Done.
