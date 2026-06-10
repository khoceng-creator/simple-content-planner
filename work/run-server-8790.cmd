@echo off
cd /d "C:\Users\immgl\Documents\Codex\2026-06-09\files-mentioned-by-the-user-pasted"
set PORT=8790
set HOST=0.0.0.0
"C:\Program Files\nodejs\node.exe" "work\static-server.js" > "work\server-8790.out.log" 2> "work\server-8790.err.log"
