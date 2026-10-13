@echo off
echo Restaurando base de datos materiax_db...
"c:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS materiax_db;"
"c:\xampp\mysql\bin\mysql.exe" -u root materiax_db < docs\materiax_db.sql
echo [OK] Base de datos materiax_db restaurada exitosamente con todos los datos.
pause
