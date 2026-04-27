const fs = require("fs");
const path = require("path");
const fetch = require("node-fetch");
const { json } = require("stream/consumers");
const DigestFetch = require("digest-fetch").default;

const filePath = "C:/Users/admin/AppData/Local/Programs/XtremeGuardParking/resources/www/storage/app/parking_members.json";



const mqtt = require("mqtt");

let mqttClient = null;



function initMQTT(env) {
  if (mqttClient) return mqttClient;

  mqttClient = mqtt.connect(env.MQTT_SERVER, {
    clientId: "XTP100001",
    clean: true,
    reconnectPeriod: 5000,
  });

  mqttClient.on("connect", () => {
    // log("INFO", "MQTT connected");
  });

  mqttClient.on("error", (err) => {
    log("ERROR", "MQTT error: " + err.message);
  });

  return mqttClient;
}

// ---- Utility logging function ----
function log(level, message) {
  const now = new Date();

  // ISO timestamp for log line
  const timestamp = now.toISOString();

  // Format date as YYYYMMDD for filename
  const datePart = now.getFullYear() +
    String(now.getMonth() + 1).padStart(2, "0") +
    String(now.getDate()).padStart(2, "0");

  const msg = `[${timestamp}] [${level}] ${message}`;
  console.log(msg);

  const LOG_DIR = path.join(__dirname, "logs");
  if (!fs.existsSync(LOG_DIR)) {
    fs.mkdirSync(LOG_DIR, { recursive: true });
  }

  const logFile = path.join(LOG_DIR, `logevents_${datePart}.log`);
  fs.appendFileSync(logFile, msg + "\n");
}

// ---- 1. Load environment settings ----
async function getEnvSettings() {
  try {
    const res = await fetch("http://127.0.0.1:8000/api/envsettings");
    if (!res.ok) throw new Error(`Failed to fetch env settings: ${res.status}`);
    const cfg = await res.json();

    log("INFO", "Environment settings loaded successfully");

    return {
      COMPANY_ID: cfg.COMPANY_ID,
      WATCH_DIR: cfg.WATCH_DIR || path.join(__dirname, "captures"),
      PARKING_CAMERA_STORAGE_PATH_NODE: cfg.PARKING_CAMERA_STORAGE_PATH_NODE || path.join(__dirname, "parking_captures"),
      API_URL: cfg.API_URL || "http://127.0.0.1:8000/api/camera_log_listener",
      MQTT_SERVER: cfg.MQTT_SERVER || cfg.host,
    };
  } catch (err) {
    log("ERROR", `Failed to load environment settings: ${err.message}`);
    throw err;
  }
}

// ---- 2. Fetch camera list from API ----
async function getCameraList() {
  try {
    log("INFO", "Fetching camera list from API...");
    const res = await fetch("http://127.0.0.1:8000/api/cameraslist");
    if (!res.ok) throw new Error(`Failed to fetch cameras: ${res.status} ${res.statusText}`);
    const json = await res.json();

    const cameras = json.data; // pagination data
    if (!Array.isArray(cameras)) throw new Error("Invalid camera list format");

    log("INFO", `Cameras received: ${cameras.map(c => c.name).join(", ")}`);
    return cameras;
  } catch (err) {
    log("ERROR", `Error fetching cameras: ${err.message}`);
    throw err;
  }
}

// ---- 3. Parse key=value text ----
function parseKeyValue(text) {
  const obj = {};
  text.split("\n").forEach(line => {
    const [key, ...rest] = line.trim().split("=");
    if (!key || rest.length === 0) return;
    obj[key.trim()] = rest.join("=").trim();
  });
  return obj;
}

// ---- 4. Send event to backend API ----
async function sendEventToAPI(env, eventData, camera) {


  /*
  
    try {
      // ---- MQTT Publish ----
      const client1 = initMQTT(env);
      const topic1 = `xtremeparking/${env.COMPANY_ID}/cameralogs/new_event`;
  
      // if (JSON.stringify(responseJson) === "{}") 
  
      let responseJsonTemp = {
        status: true, message: "success", response: {
          timestamp: eventData.timestamp,
          filename: eventData.filename,
          vehicle_id: eventData.vehicle_id,
          event_category: "Pickup",
          out_time:   eventData.timestamp,
          event_type: null,
          camera_code: eventData.camera_code,
          direction: "0",
          lane: "1",
          tag: eventData.vehicle_id,
          company_id: eventData.company_id
        }
      }
        ;
  
  
      const payload1 = JSON.stringify({ response: responseJsonTemp });
  
      client1.publish(topic1, payload1, { qos: 1 }, (err) => {
        if (err) log("ERROR", "API MQTT publish failed: " + err.message);
        else log("INFO", `API MQTT published to ${topic1}   `);
      });
  
      // Gate logic
      // if (responseJson && responseJson.status === true) {
      //   // await openGate(camera, eventData.tag);
      // }
    } catch (err) {
      log("ERROR", `MQTT Error posting event1: ${err.message}`);
    }
  
  */










  const safeData = { ...eventData, rawData: undefined };
  const clonedData = JSON.parse(JSON.stringify(safeData));


  let responseJson;
  try {
    // log("INFO", `Event posted Data: ${JSON.stringify(eventData)}`);

    const res = await fetch(env.API_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(clonedData),


    });
    // log("INFO", `API Event posted responseJson: ${JSON.stringify(clonedData)}`);


    try {
      responseJson = await res.json();
    } catch {
      const text = await res.text();
      responseJson = { message: text };
    }

    if (!res.ok) {
      log("ERROR", `API Failed to POST event: ${JSON.stringify(responseJson)}`);
      return;
    }
    log("INFO", `API Event posted successfully: ${JSON.stringify(responseJson.message)}`);
    // log("INFO", `API Event posted successfully: ${JSON.stringify(responseJson)}`);


    log("INFO", ` `);

  } catch (err) {
    log("ERROR", `API Error posting event: ${err.message}`);
  }

  try {
    // ---- MQTT Publish ----
    const client = initMQTT(env);
    const topic = `xtremeparking/${env.COMPANY_ID}/cameralogs/new_event`;



    const payload = JSON.stringify({ response: responseJson });

    client.publish(topic, payload, { qos: 1 }, (err) => {
      if (err) log("ERROR", "API MQTT publish failed: " + err.message);
      else log("INFO", `API MQTT published to ${topic}   `);
    });

    // Gate logic
    // if (responseJson && responseJson.status === true) {
    //   // await openGate(camera, eventData.tag);
    // }
  } catch (err) {
    log("ERROR", `MQTT Error posting event3: ${err.message}`);
  }


  // log("INFO", `Event  - END ------------------------------------------------------------------------`);
}
async function openGate(camera, plateNumber = "") {
  try {
    const { username, password, ip_address, name } = camera;

    const client = new DigestFetch(username, password);

    const baseUrl = `http://${ip_address}`;
    const url = new URL("/cgi-bin/trafficSnap.cgi", baseUrl);

    url.searchParams.set("action", "openStrobe");
    url.searchParams.set("channel", 1);
    url.searchParams.set("info.openType", "Normal");

    if (plateNumber) {
      url.searchParams.set("info.plateNumber", plateNumber);
    }

    // log("INFO", `[${name}] Sending gate open command...`);

    const res = await client.fetch(url.toString());
    const text = await res.text();

    log("INFO", `[${name}] Gate response: ${text.trim()}`);

  } catch (err) {
    log("ERROR", `[${camera.name}] Gate open failed: ${err.message}`);
  }
}
function formatTimestamp(unixTs) {
  const date = new Date(unixTs * 1000);

  const YYYY = date.getUTCFullYear();
  const MM = String(date.getUTCMonth() + 1).padStart(2, "0");
  const DD = String(date.getUTCDate()).padStart(2, "0");
  const hh = String(date.getUTCHours()).padStart(2, "0");
  const mm = String(date.getUTCMinutes()).padStart(2, "0");
  const ss = String(date.getUTCSeconds()).padStart(2, "0");

  return `${YYYY}${MM}${DD}${hh}${mm}${ss}000`;
}
// ---- 5. Start camera listener with retry ----
async function startCameraListener(camera, env) {
  const { username, password, name, ip_address } = camera;

  const url =
    `http://${ip_address}/cgi-bin/snapManager.cgi` +
    `?action=attachFileProc&Flags[0]=Event&Events=[All]&channel=1&heartbeat=60`;

  let attempt = 0;
  let textBuffer = "";

  let vehicleEvent = {
    plate: null,
    filenameDB: null,
    rawData: null,
    active: false,
    imageSaved: false,
    MachineName: ''
  };

  function resetVehicleEvent() {
    vehicleEvent.plate = null;
    vehicleEvent.filenameDB = null;
    vehicleEvent.rawData = null;
    vehicleEvent.active = false;
    vehicleEvent.imageSaved = false;
    vehicleEvent.MachineName = false;

    vehicleEvent.UTC = null;
    vehicleEvent.timestamp = null;

  }

  while (true) {
    attempt++;

    try {
      log("INFO", `[${name}] Connecting to camera (Attempt ${attempt})...`);

      const client = new DigestFetch(username, password);
      const res = await client.fetch(url);

      if (!res.ok) {
        throw new Error(`Stream failed: ${res.status}`);
      }

      log("INFO", `[${name}] Connected`);
      attempt = 0;

      const reader = res.body.getReader();
      const decoder = new TextDecoder();
      let buffer = Buffer.alloc(0);

      while (true) {
        const { value, done } = await reader.read();
        if (done) {
          log("WARNING", `[${name}] Camera disconnected`);
          break;
        }

        buffer = Buffer.concat([buffer, Buffer.from(value)]);
        textBuffer += decoder.decode(value, { stream: true });
        const Companyresult = loadCompanySettings(env.COMPANY_ID);
        // ---------------------------
        // 1️⃣ PLATE DETECTION
        // ---------------------------
        const lines = textBuffer.split("\n");

        for (const line of lines.slice(0, -1)) {
          if (line.includes("PlateNumber")) {

            const data = parseKeyValue(line);
            const plate = data["Events[0].TrafficCar.PlateNumber"];


            if (!plate) continue;


            const Rawdata = parseKeyValue(textBuffer);
            let MachineName = Rawdata["Events[0].TrafficCar.MachineName"];


            const unixTs = Rawdata["Events[0].UTC"] || Math.floor(Date.now() / 1000);
            const timestamp = formatTimestamp(Number(unixTs));

            let filenameDB =
              `${timestamp}_${plate}_VEHICLE_DETECTION_CAMERAIN2_FORWARD_NON_BACKGROUND.jpg`;

            filenameDB = filenameDB.replace("-", "");

            // Reset previous unfinished event
            resetVehicleEvent();

            vehicleEvent.plate = plate;
            vehicleEvent.filenameDB = filenameDB;
            vehicleEvent.rawData = Rawdata;
            vehicleEvent.MachineName = MachineName;
            vehicleEvent.UTC = timestamp;
            vehicleEvent.timestamp = timestamp;




            vehicleEvent.active = true;
            vehicleEvent.imageSaved = false;
            log("INFO", ` `);


            log("INFO", `🚗 [${name}] Vehicle  Number -----------------------------------------------------------: ${plate}`);

            // 🚪 OPEN GATE IMMEDIATELY

            const plateStatus = checkPlateStatus(plate);

            if (Companyresult.exists) {


              const cameraOut = Companyresult.company.devices[0].camera_out_name;
              const cameraIn = Companyresult.company.devices[0].camera_in_name;
              const machineName = vehicleEvent.MachineName;

              const isExit = machineName === cameraOut;
              const isEntry = machineName === cameraIn;

              const plateExists = plateStatus.exists;
              const plateBlocked = plateStatus.blocked;
              console.log("plateStatus",plateStatus);

              console.log("plateBlocked",plateBlocked);
              
              const guestAllowed = Companyresult.company.guest_vehicles;

              // console.log(machineName, cameraIn, cameraOut);
              // console.log(
              //   `plateExists: ${plateExists} | guestAllowed: ${guestAllowed} | blocked: ${plateBlocked} | isEntry: ${isEntry} | isExit: ${isExit}`
              // );              // 🚪 EXIT GATE → Always Open





              try {
                // ---- MQTT Publish ----
                const client1 = initMQTT(env);
                const topic1 = `xtremeparking/${env.COMPANY_ID}/cameralogs/new_event`;

                // if (JSON.stringify(responseJson) === "{}") 

                let responseJsonTemp = {
                  status: true, message: "success", response: {
                    timestamp: vehicleEvent.timestamp,
                    filename: vehicleEvent.filenameDB,
                    vehicle_id: vehicleEvent.plate,
                    event_category: "Pickup",
                    event_type: null,
                    camera_code: vehicleEvent.MachineName,
                    direction: "0",
                    lane: "1",
                    out_time: isEntry == true ? null : vehicleEvent.timestamp,
                    in_time: vehicleEvent.timestamp,
                    tag: vehicleEvent.plate,


                    company_id: env.company_id
                  }
                }
                  ;


                const payload1 = JSON.stringify({ response: responseJsonTemp });

                // client1.publish(topic1, payload1, { qos: 1 }, (err) => {
                //   if (err) log("ERROR", "API MQTT publish failed: " + err.message);
                //   else log("INFO", `API MQTT published111 to ${topic1}   `);
                // });

                // Gate logic
                // if (responseJson && responseJson.status === true) {
                //   // await openGate(camera, eventData.tag);
                // }
              } catch (err) {
                log("ERROR", `MQTT Error posting event2: ${err.message}`);
              }










              if (isExit) {
                log(
                  "INFO",
                  ` [EXIT GATE] Plate: ${plate} → OPEN (Always Allowed) | ${plateExists
                    ? `🔵👤🟢 Member: ${plateStatus?.name ?? ""}`
                    : "🔵🚗🟡 Guest"
                  }`
                );




                await openGate(camera, plate);
                //return;
              }
              // 🚗 ENTRY GATE LOGIC
              else if (isEntry) {

                // console.log("plateExists:", plateExists, "guestAllowed:", guestAllowed, "blocked:", plateBlocked);

                // Guest allowed
                if (!plateExists && guestAllowed) {
                  log("INFO", `🟢🚗🟡 [ENTRY GATE] Plate: ${plate} → OPEN (Guest Allowed)`);
                  await openGate(camera, plate);
                  return;
                }

                // Whitelisted member
                if (plateExists && !plateBlocked) {
                  log("INFO", `🟢👤🟢 [ENTRY GATE] Plate: ${plate} → OPEN (Whitelist Approved) 👤 Member: ${plateStatus?.name ?? ""} `);
                  await openGate(camera, plate);
                  return;
                }

                if (plateBlocked === true) {
                  console.log("Blocked True")
                } if (plateBlocked == false) {
                  console.log("Blocked False")
                }

                // Blocked member
                if (plateExists && plateBlocked == false) {
                  log("INFO", `🟢👤⛔ [ENTRY GATE] Plate: ${plate} → Member Blocked 👤 Member: ${plateStatus?.name ?? ""}   `);

                   await openGate(camera, plate);
                  return;
                  return;
                }

                // Guest not allowed
                if (!plateExists && !guestAllowed) {
                  log("INFO", `🟢🟡⛔ [ENTRY GATE] Plate: ${plate} → Guest Not Allowed`);
                   //return;
                }
              }
              else
                log("ERROR", `ENTRY or EXIT is not Defined`);
             // return;
            }
            else {
              log("ERROR", `Company JSON is not Exist`);
              //return;
            }
          }
        }

        textBuffer = lines[lines.length - 1];


        //MQTT





        // ---------------------------
        // 2️⃣ IMAGE DETECTION
        // ---------------------------
        let start, end;

        while (
          (start = buffer.indexOf(Buffer.from([0xff, 0xd8]))) !== -1 &&
          (end = buffer.indexOf(Buffer.from([0xff, 0xd9]), start)) !== -1
        ) {
          const imageBuffer = buffer.slice(start, end + 2);
          buffer = buffer.slice(end + 2);

          if (!vehicleEvent.active || !vehicleEvent.filenameDB) {
            continue;
          }

          const imageSizeKB = imageBuffer.length / 1024;
          const storagePath = path.join(
            env.PARKING_CAMERA_STORAGE_PATH_NODE,
            env.COMPANY_ID
          );

          if (!fs.existsSync(storagePath)) {
            fs.mkdirSync(storagePath, { recursive: true });
          }

          const imageType = imageSizeKB < 100 ? "PLATE" : "VEHICLE";

          const filename =
            vehicleEvent.filenameDB.replace("_BACKGROUND", "_" + imageType);

          const imagePath = path.join(storagePath, filename);

          fs.writeFileSync(imagePath, imageBuffer);

          // log("INFO", `[${name}] Saved ${imageType} image: ${filename}`);`

          // Only trigger API once on first VEHICLE image
          if (!vehicleEvent.imageSaved) {

            vehicleEvent.imageSaved = true;

            const data = vehicleEvent.rawData;//aparseKeyValue(vehicleEvent.rawData);
            const unixTs =
              data["Events[0].UTC"] || Math.floor(Date.now() / 1000);

            // const timestamp = formatTimestamp(Number(unixTs));
            const timestamp = vehicleEvent.timestamp;


            const eventData = {
              timestamp,
              filename,
              vehicle_id: vehicleEvent.plate,
              event_category: data["Events[0].TrafficCar.Category"] || null,
              event_type: data["Events[0].TrafficCar.Type"] || null,
              camera_code: data["Events[0].TrafficCar.MachineName"] || null,
              direction: data["Events[0].TrafficCar.Direction"] || null,
              lane: data["Events[0].TrafficCar.Lane"] || null,
              tag: vehicleEvent.plate,
              company_id: env.COMPANY_ID,
            };




            if (!timestamp.includes("1970"))
              sendEventToAPI(env, eventData, camera);

            // Reset after full process
            resetVehicleEvent();
          }
        }
      }

    } catch (err) {
      const waitTime = Math.min(10000 * attempt, 60000);
      log("ERROR", `[${name}] Listener error: ${err.message}`);
      log("INFO", `[${name}] Reconnecting in ${waitTime / 1000}s...`);
      await new Promise(r => setTimeout(r, waitTime));
    }
  }
}

function loadCompanySettings(companyId) {
  try {
    const filePath = path.join(
      "C:/Users/admin/AppData/Local/Programs/XtremeGuardParking/resources/www/storage/app/",
      `company_${companyId}.json`
    );

    if (!fs.existsSync(filePath)) {
      console.log("ERROR", "company.json file not found");
      return { exists: false, company: null };
    }

    const rawData = fs.readFileSync(filePath, "utf8");
    const companyData = JSON.parse(rawData);

    // Validate structure
    if (
      !companyData.company ||
      !Array.isArray(companyData.company.devices)
    ) {
      console.log("ERROR", "Invalid company.json format");
      return { exists: false, company: null };
    }

    return { exists: true, company: companyData.company };
  } catch (err) {
    console.log("ERROR", "Error reading company.json: " + err.message);
    return { exists: false, company: null };
  }
}
function normalizePlate(plate) {
  return plate
    .toUpperCase()
    .replace("DXB", "")          // remove DXB
    .replace("SH", "")          // remove DXB
    .replace("AD", "")          // remove DXB
    .replace(/[^A-Z0-9]/g, "");  // remove hyphens, spaces, etc.
}
function getPlateNumbersOnly(plate) {
  return plate.replace(/\D/g, ""); // Remove all non-digits
}

function checkPlateStatus(plateNumber) {
  try {
    const filePath = "C:/Users/admin/AppData/Local/Programs/XtremeGuardParking/resources/www/storage/app/parking_members.json";

    if (!fs.existsSync(filePath)) {
      log("ERROR", "parking_members.json file not found");
      return { exists: false, blocked: null };
    }

    const rawData = fs.readFileSync(filePath, "utf8");
    const members = JSON.parse(rawData);

    if (!Array.isArray(members)) {
      log("ERROR", "Invalid parking_members.json format");
      return { exists: false, blocked: null };
    }

    const normalizedInput = normalizePlate(plateNumber);

    // const member = members.find(m =>
    //   normalizePlate(m.plate_number) === normalizedInput
    // );

      const member = members.find(m =>
       getPlateNumbersOnly(m.plate_number) === getPlateNumbersOnly(normalizedInput)
      );

    

    log("INFO", "Member " + JSON.stringify(member));


    if (!member) {
      return { exists: false, blocked: null };
    }
    log(member.is_active);
    return { exists: true, name: member.name, active: member.blocked, blocked: member.blocked   };

  } catch (err) {
    log("ERROR", "Error reading parking_members.json: " + err.message);
    return { exists: false, blocked: null };
  }
}

function checkPlateStatus_old(plateNumber) {
  try {
    const filePath = "C:/Users/admin/AppData/Local/Programs/XtremeGuardParking/resources/www/storage/app/parking_members.json";

    if (!fs.existsSync(filePath)) {
      log("ERROR", "parking_members.json file not found");
      return { exists: false, blocked: null };
    }

    const rawData = fs.readFileSync(filePath, "utf8");
    const members = JSON.parse(rawData);

    if (!Array.isArray(members)) {
      log("ERROR", "Invalid parking_members.json format");
      return { exists: false, blocked: true };
    }

    const normalizedInput = normalizePlate(plateNumber);

    const member = members.find(m =>
      normalizePlate(m.plate_number) === normalizedInput
    );

    log("INFO", "Member " + JSON.stringify(member));


    if (!member) {
      return { exists: false, blocked: null };
    }

    return { exists: true, name: member.name, blocked: !member.is_active };

  } catch (err) {
    log("ERROR", "Error reading parking_members.json: " + err.message);
    return { exists: false, blocked: null };
  }
}
// ---- 6. Main ----
(async () => {
  try {
    const env = await getEnvSettings();
    if (!fs.existsSync(env.PARKING_CAMERA_STORAGE_PATH_NODE)) fs.mkdirSync(env.PARKING_CAMERA_STORAGE_PATH_NODE, { recursive: true });

    const cameras = await getCameraList();
    if (!cameras || cameras.length === 0) {
      log("ERROR", "No cameras found from API.");
      return;
    }

    log("INFO", `Starting listeners for ${cameras.length} cameras...`);
    await Promise.all(
      cameras.map(cam =>
        startCameraListener(cam, env).catch(err =>
          log("ERROR", `[${cam.name}] Listener initialization error: ${err.message}`)
        )
      )
    );
  } catch (err) {
    log("ERROR", `Initialization failed: ${err.message}`);
  }
})();