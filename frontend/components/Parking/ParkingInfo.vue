<template>
  <div>

    <v-snackbar v-model="snackbar" color="secondary" elevation="24">
      {{ response }}
    </v-snackbar>

    <v-dialog v-model="dialogImagePreview" max-width="80%">
      <v-card>
        <v-card-title dense class="popup_background">
          Image Preview
          <v-spacer></v-spacer>
          <v-btn icon @click="dialogImagePreview = false">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-card-title>

        <v-card-text class="d-flex justify-center">
          <v-img :src="dialogImageUrl" contain style="max-width: 80%; max-height: 80vh;"></v-img>
        </v-card-text>
      </v-card>
    </v-dialog>

    <v-row class="pt-0 mt-0">
      <v-col cols="6" class="pt-0 mt-0">
        <v-card elevation="2">
          <v-card-text class="  pa-1   ">
            <v-img style="height: 250px; " v-if="parking?.in_background_file_name"
              @click="openImagePreview(parking.parking_image_path + '/' + parking.in_background_file_name.replace('_BACKGROUND', '_VEHICLE'))"
              :src="parking.parking_image_path + '/' + parking.in_background_file_name.replace('_BACKGROUND', '_VEHICLE')"
              width="100%"></v-img>
            <div v-else
              style="height: 250px;width:100%; display: flex; align-items: center; justify-content: center; color: #888; font-size: 16px; border: 1px dashed #ccc; border-radius: 8px;">
              Photo is not Available</div>


          </v-card-text>
        </v-card>
      </v-col><v-col cols="6" class="pt-0 mt-0">
        <v-card elevation="2">
          <v-card-text class="  pa-1  d-flex ">

            <v-img v-if="parking?.out_time != null && parking?.out_background_file_name" style="height: 250px;"
              @click="openImagePreview(parking.parking_image_path + '/' + parking.out_background_file_name.replace('_BACKGROUND', '_VEHICLE'))"
              :src="parking.parking_image_path + '/' + parking.out_background_file_name.replace('_BACKGROUND', '_VEHICLE')"
              width="100%"></v-img>
            <div v-else
              style="height: 250px;width:100%; display: flex; align-items: center; justify-content: center; color: #888; font-size: 16px; border: 1px dashed #ccc; border-radius: 8px;">
              Photo is not Available</div>

          </v-card-text>
        </v-card>
      </v-col>

      <v-col cols="12" :key="key">

        <v-card elevation="2" class="  eventslistscroll table-font12" :key="parking?.id || 1">
          <v-card-text>

            <v-row><v-col cols="12">

                <!-- <div class="text-h6 text-center font-weight-bold mb-4">Vehicle Details - {{
                  parking ? parking.out_time == null ? 'Entry Only' : 'Exited' : '---'
                }}</div> -->

                <v-row class="py-1111 align-center border-b" style="border-bottom: 1px solid #353538;  ">
                  <v-col class="shrink">
                    <v-icon color="blue">mdi-car</v-icon>
                  </v-col>
                  <v-col>Vehicle Number</v-col>
                  <v-col class="text-right font-weight-medium">{{ this.parking?.log_vehicle_number ||
                    '---'
                    }}</v-col>
                </v-row>

                <v-row class="py-1111 align-center border-b" style="border-bottom: 1px solid #353538;  ">
                  <v-col class="shrink">
                    <v-icon color="red">mdi-clock-outline</v-icon>
                  </v-col>
                  <v-col>Exit Time</v-col>
                  <v-col class="text-right"> {{ this.parking ?
                    $dateFormat.formatDateTime(this.parking?.out_time) :
                    '---'
                    }} </v-col>
                </v-row>

                <v-row class="py-1111 align-center border-b" style="border-bottom: 1px solid #353538;  ">
                  <v-col class="shrink">
                    <v-icon color="green">mdi-clock-outline</v-icon>
                  </v-col>
                  <v-col>Entry Time</v-col>
                  <v-col class="text-right"> {{ this.parking ?
                    $dateFormat.formatDateTime(this.parking.in_time) :
                    '---'
                    }} </v-col>
                </v-row>

                <v-row class="py-1111 align-center border-b" style="border-bottom: 1px solid #353538;  ">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-timer-sand-complete</v-icon>
                  </v-col>
                  <v-col>Duration</v-col>
                  <v-col class="text-right"> {{ this.parking ?
                    this.parking.duration_in_hours ?
                      this.parking.duration_in_hours + ' hour(s)' :
                      '---' : '---'
                  }} </v-col>
                </v-row>
                <!-- <v-row class="py-1111 align-center" style="border-bottom:1px solid #353538;font-size: 18px;">
                  <v-col class="shrink">
                    <v-icon color="red">mdi-image-filter-center-focus-strong</v-icon>
                  </v-col>
                  <v-col>Licence Plate</v-col>
                  <v-col class="text-right">

                    <img @click="openImage(parking.image_number_plate)" v-if="parking" :src="parking.image_number_plate"
                      style="max-width: 100%; max-height: 50px; object-fit: contain;" />
                    <div v-else> ---</div>


                  </v-col>
                </v-row> -->
                <v-row class="py-1111 align-center border-b"
                  style=" border-bottom: 1px solid #353538;font-size: 18px;  ">
                  <v-col class="shrink">
                    <v-icon color="blue">mdi-cash-100</v-icon>
                  </v-col>
                  <v-col>Fee/Charges</v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.total_amount">
                      {{ this.parking.duration_per_hour_amount }} (Per Hour) X {{ this.parking.duration_in_hours
                      }} h = {{
                        this.parking.total_amount }} AED
                    </div>

                    <div v-else>---</div>



                  </v-col>
                  <v-col v-if="this.parking?.total_amount > 0 && this.parking.payment_mode == null"
                    class="text-right font-weight-bold">
                    <v-btn @click="paymentProcess('cash', parking?.id)" width="100px" height="30px" elevation="2"
                      color="green"> Cash

                    </v-btn>
                    <v-btn @click="paymentProcess('card', parking?.id)" width="100px" height="30px" elevation="2"
                      color="blue">
                      Card/Online

                    </v-btn>
                  </v-col>
                </v-row>



              </v-col>
              <!-- <v-col cols="3">

                <v-card class="background mt-0">
                  <v-card-title>
                    <div>OUT Number Plate</div>
                  </v-card-title>
                  <v-card-text class="d-flex align-center justify-center">

                    <template v-if="parking && parking.out_time && parking.out_background_file_name">
                      <v-img
                        :src="parking.parking_image_path + '/' + parking.out_background_file_name.replace('_BACKGROUND', '_PLATE')"
                        height="100" contain />

                    </template>
<template v-else>
                      <div class="text--secondary"
                        style="height:200px;display:flex;align-items:center;justify-content:center;">
                        Waiting for OUT plate…
                      </div>
                    </template>
</v-card-text>
</v-card>


<v-card class="background mt-3">
  <v-card-title>
    <div>IN Number Plate</div>
  </v-card-title>
  <v-card-text class="d-flex align-center justify-center">


    <template v-if="parking && parking.in_time && parking.in_background_file_name">

                      <v-img
                        :src="parking.parking_image_path + '/' + parking.in_background_file_name.replace('_BACKGROUND', '_PLATE')"
                        height="100" contain />
                    </template>
    <template v-else>
                      <div class="text--secondary"
                        style="height:200px;display:flex;align-items:center;justify-content:center;">
                        Waiting for IN plate…
                      </div>
                    </template>
  </v-card-text>
</v-card>

</v-col> -->
            </v-row>


          </v-card-text>
        </v-card>
        <v-row>
          <!-- Left column -->
          <v-col>
            <div>Additional Information</div>

          </v-col>


        </v-row>
        <v-card>
          <v-card-text>
            <v-row>
              <v-col cols="6">
                <v-row class="py-1111 align-center border-b" style=" border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Camera </v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.raw_camera_no">
                      {{ this.parking.raw_camera_no }}
                    </div>

                    <div v-else>---</div>
                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="6">
                <v-row class="py-1111 align-center border-b"
                  style="border-left:1px solid #353538; border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Plate Type</v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.raw_plate_type">
                      {{ this.parking.raw_plate_type }}
                    </div>

                    <div v-else>---</div>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
            <v-row>
              <v-col cols="6">
                <v-row class="py-1111 align-center border-b" style="  border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Plate Size</v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.raw_plate_size">
                      {{ this.parking.raw_plate_size }}
                    </div>

                    <div v-else>---</div>
                  </v-col>
                </v-row>
              </v-col>

              <v-col cols="6">
                <v-row class="py-1111 align-center border-b"
                  style="border-left:1px solid #353538; border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Plate Color</v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.raw_plate_color">
                      {{ this.parking.raw_plate_color }}
                    </div>

                    <div v-else>---</div>
                  </v-col>
                </v-row>

              </v-col>

              <v-col cols="6">
                <v-row class="py-1111 align-center border-b" style="  border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Country/Region</v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.raw_country_region">
                      {{ this.parking.raw_country_region }}
                    </div>

                    <div v-else>---</div>
                  </v-col>
                </v-row>
              </v-col>

              <v-col cols="6">

                <v-row class="py-1111 align-center border-b"
                  style="border-left:1px solid #353538; border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Vehicle Brand</v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.raw_vehicle_brand">
                      {{ this.parking.raw_vehicle_brand }}
                    </div>

                    <div v-else>---</div>
                  </v-col>
                </v-row>

              </v-col>

              <v-col cols="6">


                <v-row class="py-1111 align-center border-b" style=" border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Vehicle Type</v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.raw_vehicle_type">
                      {{ this.parking.raw_vehicle_type }}
                    </div>

                    <div v-else>---</div>
                  </v-col>
                </v-row>
              </v-col>

              <v-col cols="6">


                <v-row class="py-1111 align-center border-b"
                  style=" border-left:1px solid #353538;border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Vehicle Color</v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.raw_vehicle_color">
                      {{ this.parking.raw_vehicle_color }}
                    </div>

                    <div v-else>---</div>
                  </v-col>
                </v-row>

              </v-col>

              <v-col cols="6">





                <v-row class="py-1111 align-center border-b"
                  style="border-left:0px solid #353538; border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Membership </v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.membership_id">
                      {{ $utils.caps(parking.parking_members.member_type) }} -

                      <span> {{ parking.parking_members.is_active ? "Active" : "In-Active" }}</span>
                    </div>

                    <div v-else>No</div>
                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="6">





                <v-row class="py-1111 align-center border-b"
                  style="border-left:1px solid #353538; border-bottom: 1px solid #353538;"
                  v-if="this.parking?.membership_id">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Membership Name </v-col>
                  <v-col class="text-right font-weight-bold">


                    <div>

                      {{ parking.parking_members.first_name }}
                      {{ parking.parking_members.last_name }}
                    </div>


                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="6">





                <v-row class="py-1111 align-center border-b"
                  style="border-left:0px solid #353538; border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Membership Duration </v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.membership_id">
                      {{ parking.parking_members.membership_start }} To
                      {{ parking.parking_members.membership_end }}
                    </div>

                    <div v-else>---</div>
                  </v-col>
                </v-row>
              </v-col><v-col cols="6">





                <v-row class="py-1111 align-center border-b"
                  style="border-left:1px solid #353538; border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Membership Duration </v-col>
                  <v-col class="text-right font-weight-bold">


                    <div v-if="this.parking?.membership_id">
                      {{ parking.parking_members.membership_start }} To
                      {{ parking.parking_members.membership_end }}
                    </div>

                    <div v-else>---</div>
                  </v-col>
                </v-row>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>

        <v-row>
          <!-- Left column -->
          <v-col>
            <div>Membership Guest Information</div>

          </v-col>


        </v-row>
        <v-card v-if="this.parking?.parking_members_guest">
          <v-card-text>
            <v-row>
              <v-col cols="6">
                <v-row class="py-1111 align-center border-b" style=" border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Name </v-col>
                  <v-col class="text-right font-weight-bold">




                    <div>
                      {{ $utils.caps(this.parking.parking_members_guest.guest_first_name) }}
                      {{ $utils.caps(this.parking.parking_members_guest.guest_last_name) }}
                    </div>


                  </v-col>
                </v-row>
              </v-col>
              <v-col cols="6">
                <v-row class="py-1111 align-center border-b"
                  style="border-left:1px solid #353538; border-bottom: 1px solid #353538;">
                  <v-col class="shrink">
                    <v-icon color="yellow">mdi-information-slab-circle</v-icon>
                  </v-col>
                  <v-col>Address</v-col>
                  <v-col class="text-right font-weight-bold">

                    <div>
                      {{ this.parking.parking_members_guest.guest_company_details }},
                      {{ this.parking.parking_members_guest.guest_address }},
                      {{ this.parking.parking_members_guest.guest_location }}
                    </div>

                  </v-col>
                </v-row>
              </v-col>
            </v-row>

          </v-card-text>
        </v-card>
      </v-col>


    </v-row>
  </div>
</template>

<script>
export default {
  props: {
    parking: {
      type: Object,
      default: () => ({}),
    },
  },

  data() {
    return {
      key: 1,
      snackbar: false,
      response: "",
      dialogImagePreview: false,
      dialogImageUrl: '',
    };
  },
  methods: {
    openImagePreview(url) {
      this.dialogImageUrl = url;
      this.dialogImagePreview = true;
    },
    async paymentProcess(paymentMethod, id) {

      confirm("Are you sure want to Update Payment?")
      {

        try {
          const options = {
            params: {
              company_id: this.$auth.user.company_id,
              id: id,
              payment_mode: paymentMethod
            },

          };

          const { data } = await this.$axios.post(`/parking_payment_process`, options.params);

          console.log(data);


          // Update data
          if (data.status) {
            this.snackbar = true;
            this.response = "Payment is updated successfully.";
            this.key = id;


            this.$emit('close');

          }

        } catch (error) {
          this.snackbar = true;
          // this.response = error.message;
          this.response = "Error occurred while processing payment.";

        }
      }
    }
  },
};
</script>
