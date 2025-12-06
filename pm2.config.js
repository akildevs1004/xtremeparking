module.exports = {
  apps: [
    {
      name: "parking-frontend",
      cwd: "/var/www/parking/frontend",
      script: "npx",
      args: "http-server dist -p 6021",
      exec_mode: "fork",
      watch: false
    },
	{
      name: "parking-carwash-frontend",
       
      script: "npx",
      args: "http-server dist -p 6031 -c-1",
      cwd: "/var/www/carwash",
      watch: false
    },

    {
      name: "parking-queue-worker",
      cwd: "/var/www/parking/backend",
      script: "php",
      args: "artisan queue:work",
      exec_mode: "fork",
      watch: false
    },

    {
      name: "parking-mqtt-qr-listener",
      cwd: "/var/www/parking/backend",
      script: "php",
      args: "artisan mqtt:qrbackgroundlistener",
      exec_mode: "fork",
      watch: false
    },

    {
      name: "parking-camera-watcher",
      cwd: "/var/www/parking/nodescript",
      script: "node",
      args: "watchCameraImages.js",
      exec_mode: "fork",
      watch: false
    },
	{
      name: "parking-images-organizer",
      cwd: "/var/www/parking/nodescript",
      script: "node",
      args: "organize_files_by_date.js",
      exec_mode: "fork",
      watch: false
    },
	 
  ]
}
