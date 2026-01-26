const { app, BrowserWindow, screen, ipcMain, dialog } = require('electron');
const fs = require('fs');
const path = require('path');
const { logger, spawnWrapper, spawnPhpCgiWorker, runInstaller, ipv4Address, setMenu, stopServices, getCachedMachineId, isClockTampered } = require('./helpers');
const { liveStreamHelper } = require('./camera_live_stream_helper');
const { startOrganizer } = require('./camera_organize_files_by_date_helper');
const { startWatcher } = require('./camera_event_watch_helper');

app.setName('XtremeGuard Parking');
app.setAppUserModelId('XtremeGuard Parking');

let isQuitting = false;

const isDev = !app.isPackaged;
const appDir = isDev ? process.cwd() : process.resourcesPath;

const srcDirectory = path.join(appDir, 'www');

const nginxPath = path.join(appDir, 'nginx.exe');
const phpPath = path.join(srcDirectory, 'php');
const phpPathCli = path.join(phpPath, 'php.exe');
const phpCGi = path.join(phpPath, 'php-cgi.exe');

const mqttPath = path.join(appDir, 'mosquitto', 'mosquitto.exe');
const mqttConfigPath = path.join(appDir, 'mosquitto', 'mosquitto.conf');

// start "Mosquitto MQTT" cmd /k ""mosquitto.exe" -c "mosquitto.conf" -v"

let nginxWindow;

let nginxPID = null;
let schedulePID = null;
let queuePID = null;
let serverPID = null;
let MACHINE_ID = null;

let mqttListernPID = null;
let qrcodeisternPID = null;
let mqttServerPID = null;





function startServices() {

  nginxPID = spawnWrapper("[Nginx]", nginxPath, { cwd: appDir });


  // Spawn PHP workers
  [9000].forEach(port => {
    serverPID = spawnPhpCgiWorker(phpCGi, port);
  });

  schedulePID = spawnWrapper("[Application]", phpPathCli, ['artisan', 'schedule:work'], { cwd: srcDirectory });
  queuePID = spawnWrapper("[Application]", phpPathCli, ['artisan', 'queue:work'], { cwd: srcDirectory });


  mqttServerPID = spawnWrapper("[Mosquitto]", mqttPath, ['-c', mqttConfigPath, '-v'], {
    cwd: appDir
  });

  mqttListernPID = spawnWrapper("[MQTT]", phpPathCli, ['artisan', 'mqtt:subscribe'], { cwd: srcDirectory });
  qrcodeisternPID = spawnWrapper("[MQTT]", phpPathCli, ['artisan', 'mqtt:qrbackgroundlistener'], { cwd: srcDirectory });

  logger('Application', `Application started at http://${ipv4Address}:3000`);



}

ipcMain.handle('open-report-window', (event, url) => {

  fs.appendFileSync(path.join(appDir, "logs", 'ips.txt'), `${url}\n`);

  const { width, height } = screen.getPrimaryDisplay().workAreaSize;

  const reportWindow = new BrowserWindow({
    width,
    height,
    fullscreen: true,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false,
    },
  });

  reportWindow.loadURL(url);
});

function createNginxWindow() {
  const { width, height } = screen.getPrimaryDisplay().workAreaSize;

  nginxWindow = new BrowserWindow({
    width,
    height,
    autoHideMenuBar: true,
    webPreferences: {
      nodeIntegration: true,
      contextIsolation: false
    }
  });

  nginxWindow.loadURL(`http://${ipv4Address}:3000`);
  nginxWindow.maximize();

  nginxWindow.on('closed', () => {
    nginxWindow = null;
  });

  startServices();
}



app.whenReady().then(async () => {

  if (isClockTampered()) {
    dialog.showErrorBox(
      'System Time Error',
      'System date/time appears to have been changed.\n\nPlease correct your system clock and restart the application.'
    );
    app.exit(1);
    return;
  }

  MACHINE_ID = await getCachedMachineId();
  ipcMain.handle('get-machine-id', () => MACHINE_ID);

  setMenu();

  // 🚀 Show UI immediately
  const mainWindow = createNginxWindow();

  // ⏳ Heavy tasks AFTER UI
  setImmediate(() => {
    runInstaller(path.join(appDir, 'vs_redist.exe'))
      .then(() => {
        startServices();
        liveStreamHelper();
        startOrganizer();
        startWatcher();
      })
      .catch(err => {
        console.log(err.message);

      });
  });
});

// Ensure app quits cleanly and stops services
app.on('before-quit', async e => {
  if (isQuitting) return;

  e.preventDefault();
  isQuitting = true;

  fs.appendFileSync(path.join(appDir, "logs", 'SMARTQUEUE_SHUTDOWN.txt'), 'before-quit fired\n');

  await stopServices(nginxPID);
  await stopServices(schedulePID);
  await stopServices(queuePID);
  await stopServices(serverPID);

  await stopServices(mqttServerPID);
  await stopServices(mqttListernPID);
  await stopServices(qrcodeisternPID);

  app.exit(0);
});


app.on('will-quit', async () => {
  fs.appendFileSync(path.join(appDir, "logs", 'SMARTQUEUE_SHUTDOWN.txt'), 'will-quit fired\n');
  await stopServices(nginxPID);
  await stopServices(schedulePID);
  await stopServices(queuePID);
  await stopServices(serverPID);

  await stopServices(mqttServerPID);
  await stopServices(mqttListernPID);
  await stopServices(qrcodeisternPID);
});
