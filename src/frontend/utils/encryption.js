import CryptoJS from "crypto-js";

/**
 * Decrypt AES-encrypted license data
 * @param {string} encryptedData - Encrypted string
 * @param {string} secretKey - AES secret key
 * @returns {Object|null}
 */
export const decryptData = (encryptedData, secretKey) => {
  try {
    const bytes = CryptoJS.AES.decrypt(encryptedData, secretKey);
    const decryptedText = bytes.toString(CryptoJS.enc.Utf8);

    if (!decryptedText) return null;

    return JSON.parse(decryptedText);
  } catch (error) {
    console.error("License decryption failed:", error);
    return null;
  }
};
