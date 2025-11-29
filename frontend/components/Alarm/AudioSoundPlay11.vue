<template>
  <div>
    <br />
    <br />
    <br />
    <v-btn @click="playSound()" color="green">START</v-btn>

    <br />
    <v-btn @click="stopSound()" color="red">Stop</v-btn>
  </div>
</template>

<script>
export default {
  props: [
    "dialogPopupStatus",
    "notificationsMenuItemsCount",
    "dialogClosedManually",
  ],
  data() {
    return {
      audio: null,
    };
  },

  mounted() {
    // Initialize audio object after a delay
    setTimeout(() => {
      if (process.env.BACKEND_URL2) {
        this.audio = new Audio(
          process.env.BACKEND_URL2 + "/alarm_sounds/alarm-sound1.mp3"
        );
      }

      console.log(
        "Audio1",
        this.notificationsMenuItemsCount,
        this.dialogPopupStatus
      );

      // Check and play/stop sound based on conditions
      if (
        this.notificationsMenuItemsCount > 0 &&
        this.dialogPopupStatus === "true"
      ) {
        console.log("Play sound");
        this.playSound();
      } else {
        console.log("Stop sound");
        this.stopSound();
      }
    }, 2000);
    // Ensure user interaction before playing sound
    // window.addEventListener("click", this.playAudioOnUserInteraction, {
    //   once: true,
    // });
  },

  beforeUnmount() {
    // Cleanup
    // window.removeEventListener("click", this.playAudioOnUserInteraction);
  },

  methods: {
    playSound() {
      //if (!this.audio) return;

      if (process.env.BACKEND_URL2) {
        this.audio = new Audio(
          process.env.BACKEND_URL2 + "/alarm_sounds/alarm-sound1.mp3"
        );
      }

      this.audio.loop = true; // Ensures the alarm sound loops
      this.audio.play().catch((e) => console.warn("Audio play blocked", e));
    },

    playAudioOnUserInteraction() {
      if (this.audio) {
        this.audio.play().catch((e) => console.warn("Audio play blocked", e));
      }
    },

    stopSound() {
      if (this.audio) {
        this.audio.pause();
        this.audio.currentTime = 0; // Reset playback position
        this.audio = null;
      }
    },
  },
};
</script>
