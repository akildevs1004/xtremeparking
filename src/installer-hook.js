const { execFileSync } = require("child_process");
const path = require("path");

module.exports = async function (context) {
  const batPath = path.join(context.appOutDir, "create_sftp_user.bat");

  try {
    execFileSync("cmd.exe", ["/c", batPath], {
      stdio: "inherit",
      windowsHide: false,
    });
  } catch (e) {
    console.error("SFTP setup failed:", e);
  }
};
