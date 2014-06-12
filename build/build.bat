@echo on

rem *********************************************************************
rem cubi builder script. Usage: build app_name
rem *********************************************************************
cd ..\
set CUBI_DIR=%CD%
cd build
set PHING_HOME=%CUBI_DIR%\bin\phing
set PHP_CLASSPATH=%PHING_HOME%\classes

%PHING_HOME%\bin\phing -buildfile %1.xml %2

