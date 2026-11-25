# Backups (for the owner to set up)

Nothing in this file has been set up yet: it describes what to do (D13). Paths and tool names are the
usual XAMPP ones; every one is marked **verify on this machine** because installations differ.

What must be backed up:

| What | Where | Why |
|---|---|---|
| The database `smarterp_erp` | MySQL / MariaDB | every record |
| Uploaded files | `public/resources/` (product images and files, customer / vendor / employee / transaction images, attendance file `att2000.mdb`) | they are not in the database |
| The `.env` file | project root | settings and the app key; keep it private |

The code itself is in git. `database/schema/mysql-schema.sql` is only the table structure, not a backup.

## 1. Nightly database dump

Create a folder for backups on another disk, e.g. `D:\SmartERP-Backups` (**verify on this machine**).

Create `D:\SmartERP-Backups\backup.bat` (**verify every path**):

```bat
@echo off
setlocal
set MYSQLDUMP=E:\XAMPP\mysql\bin\mysqldump.exe
set DEST=D:\SmartERP-Backups
set APP=E:\XAMPP\htdocs\01_Laravel\05_SmartERP
for /f %%i in ('powershell -NoProfile -Command "Get-Date -Format yyyy-MM-dd_HHmm"') do set STAMP=%%i

rem 1) database: consistent dump without locking users out (all tables are InnoDB)
"%MYSQLDUMP%" --defaults-extra-file="%DEST%\backup.cnf" --single-transaction --routines --triggers --hex-blob --default-character-set=utf8mb4 smarterp_erp > "%DEST%\smarterp_erp_%STAMP%.sql"
if errorlevel 1 echo DATABASE BACKUP FAILED %STAMP% >> "%DEST%\backup.log"

rem 2) uploaded files
robocopy "%APP%\public\resources" "%DEST%\resources_%STAMP%" /E /R:2 /W:5 /NFL /NDL /NP >> "%DEST%\backup.log"

rem 3) keep 30 days
forfiles /p "%DEST%" /m smarterp_erp_*.sql /d -30 /c "cmd /c del @path" 2>nul
echo done %STAMP% >> "%DEST%\backup.log"
endlocal
```

Put the database login in `D:\SmartERP-Backups\backup.cnf` (not in the .bat file), readable only by the
account that runs the task:

```ini
[client]
user=backup_user
password=CHANGE_ME
host=127.0.0.1
```

Use a dedicated MySQL user that can only read: `GRANT SELECT, SHOW VIEW, TRIGGER, LOCK TABLES ON smarterp_erp.* TO 'backup_user'@'localhost' IDENTIFIED BY '...';`
(**verify** the exact privileges your MySQL / MariaDB version needs for `--single-transaction --routines`).

## 2. Schedule it (Windows Task Scheduler)

1. Start > **Task Scheduler** > **Create Task…** (not "Basic Task").
2. General: name "SmartERP backup"; **Run whether user is logged on or not**.
3. Triggers: **Daily**, e.g. 23:30.
4. Actions: **Start a program** → `D:\SmartERP-Backups\backup.bat` (**verify**).
5. Conditions: untick "Start the task only if the computer is on AC power" on a laptop.
6. Settings: "Run task as soon as possible after a scheduled start is missed".
7. Right-click the task > **Run** once and check that a new `.sql` file and `resources_…` folder appear and
   `backup.log` has no "FAILED" line.

## 3. Copy off this machine

A backup on the same computer does not survive a disk failure, theft or ransomware. At least weekly, copy
the newest dump and resources folder to another machine or an external / cloud drive (e.g. a second
`robocopy` line to a network share, or OneDrive / Google Drive folder sync of `D:\SmartERP-Backups`;
**verify on this machine**). Keep one copy that is not permanently connected.

## 4. Test a restore (do this once now, then every few months)

Restore into a **separate scratch database**, never over `smarterp_erp`:

```bat
E:\XAMPP\mysql\bin\mysql.exe -u root -p -e "CREATE DATABASE smarterp_restore_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
E:\XAMPP\mysql\bin\mysql.exe -u root -p --default-character-set=utf8mb4 smarterp_restore_test < D:\SmartERP-Backups\smarterp_erp_YYYY-MM-DD_HHMM.sql
```

Check it (counts should equal the live database at the time of the backup):

```sql
SELECT (SELECT COUNT(*) FROM smarterp_restore_test.orders) AS orders,
       (SELECT COUNT(*) FROM smarterp_restore_test.stock_items) AS stock_lines,
       (SELECT COUNT(*) FROM smarterp_restore_test.transactions) AS transactions,
       (SELECT MAX(created_at) FROM smarterp_restore_test.transactions) AS newest_entry;
```

Optionally point a copy of the app at it (`DB_DATABASE=smarterp_restore_test` in a copied `.env`) and open
a few pages. Afterwards: `DROP DATABASE smarterp_restore_test;`

Write down the date of each successful restore test.
