<template>
  <div>

    <audio ref="audioPlayer" :src="audioFile"></audio>

    <!-- Controls -->

    <!-- {{ notificationsMenuItemsCount }}
    <button @click="playAudio" :disabled="isPlaying">Play</button>
    <button @click="pauseAudio" :disabled="!isPlaying">Pause</button>
    <button @click="stopAudio" :disabled="!isPlaying">Stop</button> -->
  </div>
</template>

<script>
export default {
  props: ["notificationsMenuItemsCount"],
  data() {
    return {
      audio: null,
      isPlaying: false,
      audioFile: this.$env.settings.BACKEND_URL2 + "/alarm_sounds/alarm-sound1.mp3", // Replace with your audio file path
    };
  },
  watch: {
    notificationsMenuItemsCount() { },
  },
  mounted() {
    // Initialize audio object after a delay
    //setTimeout(() => {
    try {
      if (this.$env.settings.BACKEND_URL2) {
        this.audioFile =
          this.$env.settings.BACKEND_URL2 + "/alarm_sounds/alarm-sound1.mp3";
      }

      console.log(
        "this.notificationsMenuItemsCount ",
        this.notificationsMenuItemsCount
      );
      if (parseInt(this.notificationsMenuItemsCount) > 0) {
        this.playAudio();
      }
      if (parseInt(this.notificationsMenuItemsCount) == 0) {
        this.stopAudio();
      }

    } catch (e) { }
    //   }, 1000 * 5);
    // Ensure user interaction before playing sound
    // window.addEventListener("click", this.playAudioOnUserInteraction, {
    //   once: true,
    // });
  },

  beforeUnmount() {
    // Cleanup
    // window.removeEventListener("click", this.playAudioOnUserInteraction);
  },

  created() {
    this.audioFile =
      this.$env.settings.BACKEND_URL2 + "/alarm_sounds/alarm-sound1.mp3";
  },
  methods: {
    // playAudioOnUserInteraction() {
    //   if (this.audio) {
    //     this.audio.play().catch((e) => console.warn("Audio play blocked", e));
    //   }
    // },
    // playAudio1() {
    //   console.log("playAudio");
    //   this.$refs.audioPlayer.play();
    //   this.isPlaying = true;
    //   if (this.$refs.audioPlayer) {
    //     this.$refs.audioPlayer.play();
    //     this.isPlaying = true;
    //   }
    // },

    playAudio() {
      const audio = this.$refs.audioPlayer;

      setTimeout(() => {
        audio
          .play()
          .then(() => {
            this.isPlaying = true;
          })
          .catch((error) => {
            console.error("Error playing audio:", error);
            // alert(
            //   "Please Stay on Dashboard page. Then only Alarm sound will be work."
            // ); // Notify the user
          });
      }, 1000 * 5);
    },
    pauseAudio() {
      if (this.$refs.audioPlayer) {
        this.$refs.audioPlayer.pause();
        this.isPlaying = false;
      }
    },
    stopAudio() {
      try {
        console.log("stopAudio");
        if (this.$refs.audioPlayer) {
          this.$refs.audioPlayer.pause();
          this.$refs.audioPlayer.currentTime = 0; // Reset audio to the beginning
          this.isPlaying = false;
        }
      } catch (e) { }
    },
  },
};
</script>
