<template>
  <v-card class="pa-6 mx-auto" max-width="420" elevation="10">
    <v-card-text>
      <div class="text-center mb-6">

        <div class="text-h6 font-weight-bold">Activate License</div>
        <div class="text-body-2 error--text">
          Your trial has expired. Please activate your license to continue.
        </div>
      </div>

      <!-- Machine Code -->
      <v-text-field label="Machine Code" :value="machineId || ''" readonly outlined dense append-icon="mdi-content-copy"
        @click:append="copyMachineId" />

      <!-- License Key -->
      <v-text-field v-model="license_key" label="License Key" outlined dense required :error="!!licenseError" />

      <v-alert v-if="licenseError" type="error" dense text class="mt-2">
        {{ licenseError }}
      </v-alert>

      <v-btn color="primary" block class="mt-4" :loading="processing" :disabled="processing" @click="submit">
        Activate License
      </v-btn>
    </v-card-text>
  </v-card>
</template>

<script>

import { decryptData } from '@/utils/encryption'

let electronClipboard = null
if (typeof window !== 'undefined' && window.process?.type === 'renderer') {
  electronClipboard = window.require('electron').clipboard
}

export default {
  name: 'LicenseActivationForm',

  // props: {
  //   machineId: { type: String, default: null },
  //   // Provide a URL string or a Ziggy route name resolved by parent.
  //   // Example: "/license" or route('license.update')
  //   licenseUpdateUrl: { type: String, required: true },
  // },

  data() {
    return {
      machineId: '',
      license_key: '',
      processing: false,
      licenseError: null,
    }
  },

  async mounted() {


    this.machineId = "";
    this.licenseUpdateUrl = "updateLicenseInfo";

    const isElectron =
      typeof window !== 'undefined' &&
      window.process?.type === 'renderer'

    if (!isElectron) return

    const { ipcRenderer } = window.require('electron')
    this.machineId = await ipcRenderer.invoke('get-machine-id')
  },

  methods: {
    copyMachineId() {
      if (!this.machineId || !electronClipboard) return
      electronClipboard.writeText(this.machineId)
    },

    async submit() {
      this.licenseError = null

      if (!this.machineId || !this.license_key) {

        console.log(this.machineId, this.license_key);

        this.licenseError = 'License key or machine ID is missing.'
        return
      }

      let license
      try {
        license = decryptData(this.license_key, this.machineId)
      } catch (e) {
        license = null
      }

      if (!license) {
        this.licenseError = 'Invalid License Key'
        return
      }

      // Client-side expiry check (optional but matches your React behavior)
      if (new Date() > new Date(license.expiry_date)) {
        this.licenseError = 'License expired'
        return
      }

      const payload = {
        license_key: this.license_key,
        machine_id: this.machineId,
        expiry_date: license.expiry_date,
      }



      this.processing = true;



      // Inertia request WITHOUT useForm
      this.processing = true
      this.licenseError = null

      this.$axios
        .post(this.licenseUpdateUrl, payload)
        .then((response) => {
          //  alert("Activation Success")

          this.licenseError = "Activation Success."
          this.$emit("openloginpage")
        })
        .catch((error) => {
          console.error(error)

          this.licenseError =
            error.response?.data?.message ||
            "Activation failed on the server."
        })
        .finally(() => {
          this.processing = false
        })

    },
  },
}
</script>
