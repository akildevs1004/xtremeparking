<template>
  <div style="max-width: 900px; margin: 24px auto; padding: 16px;">
    <h2 style="margin: 0 0 12px;">Decrypt String (Vue.js 2)</h2>

    <div style="display: grid; gap: 12px;">
      <label>
        <div style="font-weight: 600; margin-bottom: 6px;">Encrypted Text</div>
        <textarea v-model="cipherText" rows="10" style="width: 100%; padding: 10px; font-family: monospace;"
          placeholder="Paste Base64/Hex encrypted string here..." />
      </label>

      <label>
        <div style="font-weight: 600; margin-bottom: 6px;">Password</div>
        <input v-model="password" type="password" style="width: 100%; padding: 10px;"
          placeholder="Password (secret key)" />
      </label>

      <div style="display: flex; gap: 10px; align-items: center;">
        <button @click="decrypt" :disabled="busy || !cipherText || !password"
          style="padding: 10px 14px; cursor: pointer;">
          {{ busy ? "Decrypting..." : "Decrypt" }}
        </button>

        <button @click="resetAll" style="padding: 10px 14px; cursor: pointer;">
          Reset
        </button>

        <span v-if="status" style="opacity: 0.85;">{{ status }}</span>
      </div>

      <label>
        <div style="font-weight: 600; margin-bottom: 6px;">Decrypted Output</div>
        <textarea :value="plainText" rows="10" readonly style="width: 100%; padding: 10px; font-family: monospace;"
          placeholder="Decrypted output will appear here..." />
      </label>

      <div v-if="error" style="color: #b00020; white-space: pre-wrap;">
        {{ error }}
      </div>

      <details>
        <summary style="cursor:pointer;">Detection info</summary>
        <pre style="white-space: pre-wrap;">{{ info }}</pre>
      </details>
    </div>
  </div>
</template>

<script>
import CryptoJS from "crypto-js";

export default {
  name: "DecryptPage",
  data() {
    return {
      cipherText: "",
      password: "",
      plainText: "",
      error: "",
      status: "",
      info: "",
      busy: false,
    };
  },
  methods: {
    resetAll() {
      this.cipherText = "";
      this.password = "";
      this.plainText = "";
      this.error = "";
      this.status = "";
      this.info = "";
    },

    detectFormat(s) {
      const t = (s || "").trim();

      const looksBase64 = /^[A-Za-z0-9+/=\s]+$/.test(t) && t.length > 16;
      const looksHex = /^[0-9a-fA-F\s]+$/.test(t) && t.replace(/\s/g, "").length % 2 === 0;

      const startsWithSalted =
        looksBase64 && t.replace(/\s/g, "").startsWith("U2FsdGVkX1"); // "Salted__" in Base64

      return {
        looksBase64,
        looksHex,
        startsWithSalted,
        length: t.length,
      };
    },

    tryCryptoJSAES(cipherText, password) {
      // CryptoJS password-based AES format (OpenSSL compatible), often starts with "U2FsdGVkX1"
      const bytes = CryptoJS.AES.decrypt(cipherText, password);
      return bytes.toString(CryptoJS.enc.Utf8);
    },

    tryAESCBC_IV_PREFIXED(base64Data, password) {
      // Base64( IV(16) + ciphertext ), Key = SHA256(password)
      const raw = CryptoJS.enc.Base64.parse(base64Data);

      // first 16 bytes => 4 words
      const iv = CryptoJS.lib.WordArray.create(raw.words.slice(0, 4), 16);
      const ct = CryptoJS.lib.WordArray.create(raw.words.slice(4), raw.sigBytes - 16);

      const key = CryptoJS.SHA256(password);

      const decrypted = CryptoJS.AES.decrypt({ ciphertext: ct }, key, {
        iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7,
      });

      return decrypted.toString(CryptoJS.enc.Utf8);
    },

    hexToBase64(hex) {
      const clean = hex.replace(/\s/g, "");
      const words = CryptoJS.enc.Hex.parse(clean);
      return CryptoJS.enc.Base64.stringify(words);
    },

    normalizeInput(s) {
      return (s || "").trim();
    },

    async decrypt() {
      this.busy = true;
      this.error = "";
      this.status = "Trying to decrypt…";
      this.plainText = "";
      this.info = "";

      try {
        let cipher = this.normalizeInput(this.cipherText);
        const pass = this.password;

        const det = this.detectFormat(cipher);
        this.info = JSON.stringify(det, null, 2);

        // If HEX, convert to Base64 for Option B (common for IV+ciphertext storage)
        let base64Cipher = cipher;
        if (det.looksHex && !det.looksBase64) {
          base64Cipher = this.hexToBase64(cipher);
          this.status = "Detected HEX → converted to Base64. Trying decrypt…";
        }

        // 1) Try CryptoJS password-AES (Option A)
        try {
          const plainA = this.tryCryptoJSAES(cipher, pass);
          if (plainA && plainA.length) {
            this.plainText = this.parseIfJson(plainA);
            this.status = "Decrypted using CryptoJS AES (password format).";
            this.busy = false;
            return;
          }
        } catch (e) { }

        // 2) Try AES-CBC with IV prefixed (Option B)
        try {
          const plainB = this.tryAESCBC_IV_PREFIXED(base64Cipher, pass);
          if (plainB && plainB.length) {
            this.plainText = this.parseIfJson(plainB);
            this.status = "Decrypted using AES-CBC (IV prefixed) + SHA256(password) key.";
            this.busy = false;
            return;
          }
        } catch (e) { }

        // If both failed:
        this.status = "Failed.";
        this.error =
          "Could not decrypt.\n\nMost common reasons:\n" +
          "1) The encrypted value is not valid Base64/Hex (your pasted CCTV export may be binary).\n" +
          "2) The encryption method is different (AES-GCM, different IV handling, different key derivation, etc.).\n" +
          "3) Wrong password.\n\n" +
          "If you copied this from a CCTV file, export it as TEXT/Base64/Hex first (not raw/binary).";
      } finally {
        this.busy = false;
      }
    },

    parseIfJson(s) {
      try {
        return JSON.stringify(JSON.parse(s), null, 2);
      } catch {
        return s;
      }
    },
  },
};
</script>

<style scoped>
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
