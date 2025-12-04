<template>
  <NoAccess v-if="!$pagePermission.can('company_view', this)" />
  <div v-else style="width: 100%; margin-top: -20px">
    <div v-if="!preloader">
      <div class="text-center ma-2">
        <v-snackbar v-model="snackbar" top="top" color="secondary" elevation="24">
          {{ response }}
        </v-snackbar>
      </div>

      <v-row>
        <v-col :style="'max-width:350px;height:800px'">
          <v-row><v-col>
              <v-card><v-card-text class="text-center justify-center">
                  <v-row>
                    <v-col>
                      <v-img @click="onpick_attachment" class="company-profile-picture" style="
                          width: 180px;
                          height: 180px;
                          border: 1px solid #ddd;
                          border-radius: 50%;
                          margin: 0 auto;
                        " :src="previewImage ||
                          company_payload.logo ||
                          '/no-image.PNG'
                          "></v-img>
                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col>
                      <!-- <v-btn @click="onpick_attachment" text v-if="!upload.name" color="primary" elevation="0" outlined
                        plain rounded>Change Logo</v-btn> -->

                      <input required type="file" @change="attachment" style="display: none" accept="image/*"
                        ref="attachment_input" />














                      <v-btn @click="onpick_attachment" text v-if="!upload.name" color="primary" elevation="0" outlined
                        plain rounded>Upload Logo</v-btn>

                      <v-btn text color="primary" elevation="0" outlined plain rounded v-if="upload.name"
                        style="width: 100px" @click="cancelAttachment">Cancel
                      </v-btn>
                      <v-btn text color="primary" elevation="0" outlined plain rounded v-if="upload.name"
                        style="width: 100px" @click="update_company">{{ !upload.name ? "Change Logo" : "Submit" }}
                      </v-btn>

                      <input required type="file" @change="attachment" style="display: none" accept="image/*"
                        ref="attachment_input" />

                      <span v-if="errors && errors.logo" class="text-danger mt-2">{{ errors.logo[0] }}</span>









                    </v-col>
                  </v-row>
                  <v-row>
                    <v-col>
                      <h3>{{ company_payload.name }}</h3>
                      <div>({{ company_payload.company_code }})</div>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </v-col></v-row>
          <v-row class="mt-0 pt-0"><v-col>
              <v-card><v-card-text class="text-center justify-center">
                  <v-row class="ma-0 pa-0">
                    <v-col>
                      <h3>{{ contact_payload.name }}</h3>
                      <div>({{ contact_payload.position }})</div>
                    </v-col>
                  </v-row>
                  <div class="text-left" style="font-size: 15px; line-height: 25px">
                    <div style="border-bottom: 0px solid #443f6f; color: #a19797">
                      <v-icon size="15" color="red">mdi-phone</v-icon>
                      <span style="color: #fff"> Phone : </span>
                      {{ contact_payload.number }}
                    </div>
                    <div style="border-bottom: 0px solid #443f6f; color: #a19797">
                      <v-icon size="15" color="blue">mdi-at</v-icon>
                      <span style="color: #fff">Email : </span>
                      <span>{{ user_payload.email }}</span>
                    </div>
                    <div style="border-bottom: 0px solid #443f6f; color: #a19797">
                      <v-icon size="15" color="green">mdi-whatsapp</v-icon>
                      <span style="color: #fff">Whatsapp : </span>{{ contact_payload.whatsapp }}
                    </div>
                  </div>

                  <v-row style="margin-top: 0px; line-height: 20px" class="text-left">
                    <v-col cols="12" style="color: #a19797">
                      <h3 class="text-left" style="color: #ddd">
                        <v-icon size="15" color="red">mdi-map-marker</v-icon>
                        Address:
                      </h3>
                      <div style="padding-left: 18px">
                        {{ company_payload.p_o_box_no }}<br />
                        {{ geographic_payload.location }}
                      </div>
                    </v-col>
                  </v-row>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>

          <v-row class="mt-0 pt-0"><v-col>
              <v-card><v-card-text class="text-center justify-center">
                  <v-row style="line-height: 10px" class="text-left">
                    <v-col cols="6">
                      <v-icon size="15" color="green">mdi-calendar-range</v-icon>
                      Account Start Date</v-col>
                    <v-col cols="6" style="color: #a19797">:
                      {{
                        $dateFormat.formatDate(company_payload.member_from)
                      }}</v-col>

                    <v-col cols="6">
                      <v-icon size="15" color="red">mdi-calendar-range</v-icon>
                      Account Exp Date
                    </v-col>
                    <v-col cols="6" style="color: #a19797">:
                      {{
                        $dateFormat.formatDate(company_payload.expiry)
                      }}</v-col>

                    <!--<v-col cols="6">
                      <v-icon size="15">mdi-office-building-marker-outline</v-icon>
                      Max Branches
                    </v-col>
                    <v-col cols="6" style="color: #a19797">: {{ company_payload.max_branches }}</v-col>

                    <v-col cols="6">
                      <v-icon size="15">mdi-cellphone-text</v-icon>
                      Max Devices
                    </v-col>
                    <v-col cols="6" style="color: #a19797">: {{ company_payload.max_devices }}</v-col>

                    <v-col cols="6">
                      <v-icon size="15">mdi-account-tie</v-icon>
                      Max Employees
                    </v-col>

                    <v-col cols="6" style="color: #a19797">: {{ company_payload.max_employee }}</v-col> -->
                  </v-row>
                </v-card-text>
              </v-card>
            </v-col>
          </v-row>
        </v-col>
        <v-col class="pl-0">
          <v-card>
            <v-tabs class="pt-3" :vertical="vertical" :style="'height:815px'">
              <v-tabs-slider color="violet"></v-tabs-slider>


              <v-tab>
                <span>Company Profile</span>
              </v-tab>
              <v-tab>
                <span>Business License</span>
              </v-tab>
              <v-tab>
                <span>Contact</span>
              </v-tab>
              <v-tab>
                <span>Documents</span>
              </v-tab>
              <v-tab>
                <span>Password</span>
              </v-tab>



              <v-tab-item>
                <v-card flat>
                  <v-card-text>
                    <v-row>
                      <v-col cols="2" style="margin: auto">
                        <v-card elevation="0" class="ml-1 mr-1" style="text-align: center; margin: auto">
                          <div style="width: 100%; text-align: center">
                            <div class="pb-2">
                              <v-img @click="onpick_attachment" class="company-profile-picture" style="
                                  width: 200px;
                                  height: 200px;
                                  border: 1px solid #ddd;
                                  border-radius: 50%;
                                  margin: 0 auto;
                                " :src="previewImage ||
                                  company_payload.logo ||
                                  '/no-image.PNG'
                                  "></v-img>
                            </div>
                          </div>
                          <v-btn @click="onpick_attachment" text v-if="!upload.name" color="primary" elevation="0"
                            outlined plain rounded>Upload Logo</v-btn>

                          <v-btn text color="primary" elevation="0" outlined plain rounded v-if="upload.name"
                            style="width: 100px" @click="cancelAttachment">Cancel
                          </v-btn>
                          <v-btn text color="primary" elevation="0" outlined plain rounded v-if="upload.name"
                            style="width: 100px" @click="update_company">{{ !upload.name ? "Change Logo" : "Submit" }}
                          </v-btn>
                        </v-card>

                        <input required type="file" @change="attachment" style="display: none" accept="image/*"
                          ref="attachment_input" />

                        <span v-if="errors && errors.logo" class="text-danger mt-2">{{ errors.logo[0] }}</span>
                      </v-col>
                      <v-col cols="10">
                        <v-card elevation="0">
                          <v-card-text>
                            <v-row>
                              <v-col cols="4">
                                <v-text-field readonly label="Company Code" dense outlined
                                  v-model="company_payload.company_code" hide-details></v-text-field>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field label="Company Name" readonly dense outlined hide-details
                                  v-model="company_payload.name"></v-text-field>
                                <span v-if="errors && errors.name" class="text-danger mt-2">{{ errors.name[0] }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field label="Company Email" readonly dense outlined hide-details
                                  v-model="user_payload.email"></v-text-field>
                                <span v-if="errors && errors.email" class="text-danger mt-2">{{ errors.email[0]
                                }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field readonly label="Contact Person Name" dense outlined
                                  v-model="contact_payload.name" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.name" class="text-danger mt-2">{{ errors.name[0] }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field readonly label="Contact Person Number" dense outlined
                                  v-model="contact_payload.number" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.number" class="text-danger mt-2">{{ errors.number[0]
                                }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field readonly label="Contact Person Position" dense outlined
                                  v-model="contact_payload.position" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.position" class="text-danger mt-2">{{ errors.position[0]
                                }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field readonly label="Whatsapp (with Country Code ex: 97199XXX)" dense outlined
                                  v-model="contact_payload.whatsapp" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.whatsapp" class="text-danger mt-2">{{ errors.whatsapp[0]
                                }}</span>
                              </v-col>
                              <v-col cols="4">
                                <v-text-field readonly label="Mobile Number" color="primary" dense outlined
                                  v-model="company_payload.mol_id" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.mol_id" class="text-danger mt-2">{{ errors.mol_id[0]
                                }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field readonly label="P.O Box" color="primary" dense outlined
                                  v-model="company_payload.p_o_box_no" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.p_o_box_no" class="text-danger mt-2">{{
                                  errors.p_o_box_no[0]
                                }}</span>
                              </v-col>
                              <v-col cols="4">
                                <v-text-field readonly label="Latitude" dense outlined v-model="geographic_payload.lat"
                                  hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.lat" class="text-danger mt-2">{{ errors.lat[0] }}</span>
                              </v-col>
                              <v-col cols="4">
                                <v-text-field readonly label="Longitude" dense outlined v-model="geographic_payload.lon"
                                  hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.lon" class="text-danger mt-2">{{ errors.lon[0] }}</span>
                              </v-col>
                              <v-col cols="4">
                                <v-textarea readonly label="Location" dense outlined height="30px"
                                  v-model="geographic_payload.location" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    ">
                                </v-textarea>
                                <span v-if="errors && errors.location" class="text-danger mt-2">{{ errors.location[0]
                                }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field label="Member From" readonly dense outlined hide-details
                                  v-model="company_payload.member_from"></v-text-field>
                                <span v-if="errors && errors.member_from" class="text-danger mt-2">{{
                                  errors.member_from[0] }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field label="Expiry Date" readonly dense outlined hide-details
                                  v-model="company_payload.expiry"></v-text-field>
                                <span v-if="errors && errors.expiry" class="text-danger mt-2">{{ errors.expiry[0]
                                }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field label="Max Branches" readonly dense outlined hide-details
                                  v-model="company_payload.max_branches"></v-text-field>
                                <span v-if="errors && errors.max_branches" class="text-danger mt-2">{{
                                  errors.max_branches[0] }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field label="Max Employees" readonly dense outlined hide-details
                                  v-model="company_payload.max_employee"></v-text-field>
                                <span v-if="errors && errors.max_employee" class="text-danger mt-2">{{
                                  errors.max_employee[0] }}</span>
                              </v-col>

                              <v-col cols="4">
                                <v-text-field label="Max Devices" readonly dense outlined hide-details
                                  v-model="company_payload.max_devices"></v-text-field>
                                <span v-if="errors && errors.max_devices" class="text-danger mt-2">{{
                                  errors.max_devices[0] }}</span>
                              </v-col>
                            </v-row>
                          </v-card-text>
                        </v-card>
                      </v-col>
                      <v-col cols="12" style="display: none">
                        <v-card elevation="2" style="height: 500px">
                          <v-card-title>Contact Details</v-card-title>

                          <v-card-text>
                            <v-row>
                              <v-col cols="6">
                                <v-text-field label="Contact Person Name" dense outlined v-model="contact_payload.name"
                                  hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.name" class="text-danger mt-2">{{ errors.name[0] }}</span>
                              </v-col>

                              <v-col cols="6">
                                <v-text-field label="Contact Person Number" dense outlined
                                  v-model="contact_payload.number" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.number" class="text-danger mt-2">{{ errors.number[0]
                                }}</span>
                              </v-col>

                              <v-col cols="6">
                                <v-text-field label="Contact Person Position" dense outlined
                                  v-model="contact_payload.position" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.position" class="text-danger mt-2">{{ errors.position[0]
                                }}</span>
                              </v-col>

                              <v-col cols="6">
                                <v-text-field label="Whatsapp (with Country Code ex: 97199XXX)" dense outlined
                                  v-model="contact_payload.whatsapp" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.whatsapp" class="text-danger mt-2">{{ errors.whatsapp[0]
                                }}</span>
                              </v-col>
                              <v-col cols="6" md="6" sm="6">
                                <v-text-field label="Mobile Number" color="primary" dense outlined
                                  v-model="company_payload.mol_id" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.mol_id" class="text-danger mt-2">{{ errors.mol_id[0]
                                }}</span>
                              </v-col>

                              <v-col cols="6" md="6" sm="6">
                                <v-text-field label="P.O Box" color="primary" dense outlined
                                  v-model="company_payload.p_o_box_no" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.p_o_box_no" class="text-danger mt-2">{{
                                  errors.p_o_box_no[0]
                                }}</span>
                              </v-col>
                              <v-col cols="6">
                                <v-text-field label="Latitude" dense outlined v-model="geographic_payload.lat"
                                  hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.lat" class="text-danger mt-2">{{ errors.lat[0] }}</span>
                              </v-col>
                              <v-col cols="6">
                                <v-text-field label="Longitude" dense outlined v-model="geographic_payload.lon"
                                  hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-text-field>
                                <span v-if="errors && errors.lon" class="text-danger mt-2">{{ errors.lon[0] }}</span>
                              </v-col>
                              <v-col cols="12">
                                <v-textarea label="Location" dense outlined height="50px"
                                  v-model="geographic_payload.location" hide-details :disabled="!$pagePermission.can('company_edit', this)
                                    ">
                                </v-textarea>
                                <span v-if="errors && errors.location" class="text-danger mt-2">{{ errors.location[0]
                                }}</span>
                              </v-col>
                              <v-col>
                                <v-autocomplete class="pb-0" hide-details v-model="geographic_payload.utc_time_zone"
                                  outlined dense label="Time Zone(Ex:UTC+)" :items="getTimezones()" item-value="key"
                                  item-text="text" :disabled="!$pagePermission.can('company_edit', this)
                                    "></v-autocomplete>
                              </v-col>
                            </v-row>
                          </v-card-text>
                        </v-card>
                      </v-col>
                    </v-row>


                  </v-card-text>
                </v-card>
              </v-tab-item>
              <v-tab-item>
                <v-card flat>
                  <v-card-text>
                    <v-row>
                      <v-col cols="4">
                        <v-text-field label="License" dense outlined v-model="company_payload.business_license"
                          hide-details :disabled="!$pagePermission.can('company_edit', this)"></v-text-field>
                        <span v-if="errors && errors.business_license" class="text-danger mt-2">{{
                          errors.business_license[0] }}</span>
                      </v-col>

                      <v-col cols="4">
                        <!-- <label class="col-form-label">
                              Contact Person Number
                            </label>
                            <span class="text-danger">*</span> -->

                        <v-autocomplete v-model="company_payload.business_license_type" outlined hide-details dense
                          :items="[
                            'Commercial License',
                            'Industrial License',
                            'Proffessional License',
                          ]" label="License type" clearable>
                        </v-autocomplete>
                        <span v-if="errors && errors.business_license_type" class="text-danger mt-2">{{
                          errors.License_type[0] }}</span>
                      </v-col>
                      <v-col cols="4">
                        <!-- <label class="col-form-label">Mol ID</label>
                        <span class="text-danger">*</span> -->
                        <v-autocomplete label="Emirati" color="primary" dense outlined
                          v-model="company_payload.business_emirati" hide-details
                          :disabled="!$pagePermission.can('company_edit', this)" :items="[
                            'Dubai',
                            'Abu Dhabi',
                            'Sharjah',
                            'Ajman',
                            'Umm Al Quwain',
                            'Ras Al Khaimah',
                            'Fujairah',
                          ]"></v-autocomplete>
                        <span v-if="errors && errors.business_emirati" class="text-danger mt-2">{{
                          errors.business_emirati[0] }}</span>
                      </v-col>
                      <v-col cols="4">
                        <!-- <label class="col-form-label">
                              Contact Person Position
                            </label>
                            <span class="text-danger">*</span> -->

                        <v-menu v-model="menu_start_date" :close-on-content-click="false" :nudge-right="40"
                          transition="scale-transition" offset-y min-width="auto">
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field outlined dense v-model="company_payload.business_license_issue_date
                              " label="License Start" append-icon="mdi-calendar" readonly v-bind="attrs"
                              v-on="on"></v-text-field>
                          </template>
                          <v-date-picker no-title v-model="company_payload.business_license_issue_date
                            " @input="menu_start_date = false"></v-date-picker>
                        </v-menu>

                        <span v-if="errors && errors.business_license_issue_date" class="text-danger mt-2">{{
                          errors.business_license_issue_date[0] }}</span>
                      </v-col>

                      <v-col cols="4">
                        <v-menu v-model="menu_end_date" :close-on-content-click="false" :nudge-right="40"
                          transition="scale-transition" offset-y min-width="auto">
                          <template v-slot:activator="{ on, attrs }">
                            <v-text-field outlined dense v-model="company_payload.business_license_expiry_date
                              " label="License End" append-icon="mdi-calendar" readonly v-bind="attrs"
                              v-on="on"></v-text-field>
                          </template>
                          <v-date-picker no-title :min="company_payload.business_license_issue_date" v-model="company_payload.business_license_expiry_date
                            " @input="menu_end_date = false"></v-date-picker>
                        </v-menu>
                        <!-- <label class="col-form-label">
                              Whatsapp (with Country Code ex: 9199XXX)
                            </label>
                            <span class="text-danger">*</span> -->

                        <span v-if="errors && errors.business_license_expiry_date" class="text-danger mt-2">{{
                          errors.business_license_expiry_date[0] }}</span>
                      </v-col>

                      <v-col cols="4">
                        <!-- <label class="col-form-label">P.O Box</label>
                        <span class="text-danger">*</span> -->
                        <v-text-field label="Makeem Number" color="primary" dense outlined
                          v-model="company_payload.business_makeem_number" hide-details
                          :disabled="!$pagePermission.can('company_edit', this)"></v-text-field>
                        <span v-if="errors && errors.business_makeem_number" class="text-danger mt-2">{{
                          errors.business_makeem_number[0]
                          }}</span>
                      </v-col>
                    </v-row>

                    <v-row>
                      <v-col cols="12">
                        <div class="text-right">
                          <v-btn v-if="$pagePermission.can('company_edit', this)" small :loading="loading"
                            color="primary" @click="update_company_business_license()">
                            Update
                          </v-btn>
                        </div>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-tab-item>

              <v-tab-item>
                <v-card flat>
                  <v-card-text>
                    <v-row>
                      <v-col cols="4">
                        <v-text-field label="Name" dense outlined v-model="contact_payload_update.name" hide-details
                          :disabled="!$pagePermission.can('company_edit', this)"></v-text-field>
                        <span v-if="errors && errors.name" class="text-danger mt-2">{{ errors.name[0] }}</span>
                      </v-col>
                      <v-col cols="4">
                        <v-text-field label="Contact Number" dense outlined v-model="contact_payload_update.number"
                          hide-details :disabled="!$pagePermission.can('company_edit', this)"></v-text-field>
                        <span v-if="errors && errors.number" class="text-danger mt-2">{{ errors.number[0] }}</span>
                      </v-col>
                      <v-col cols="4">
                        <v-text-field label="Position" dense outlined v-model="contact_payload_update.position"
                          hide-details :disabled="!$pagePermission.can('company_edit', this)"></v-text-field>
                        <span v-if="errors && errors.position" class="text-danger mt-2">{{ errors.position[0] }}</span>
                      </v-col>
                      <v-col cols="4">
                        <v-text-field label="Whatsapp Number" dense outlined v-model="contact_payload_update.whatsapp"
                          hide-details :disabled="!$pagePermission.can('company_edit', this)"></v-text-field>
                        <span v-if="errors && errors.whatsapp" class="text-danger mt-2">{{ errors.whatsapp[0] }}</span>
                      </v-col>
                    </v-row>
                    <v-row>
                      <v-col cols="12">
                        <div class="text-right">
                          <v-btn v-if="$pagePermission.can('company_edit', this)" small :loading="loading"
                            color="primary" @click="update_contact()">
                            Update
                          </v-btn>
                        </div>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-tab-item>

              <v-tab-item>
                <CompanyDocument />
              </v-tab-item>

              <v-tab-item>
                <v-container>
                  <v-row>
                    <v-col cols="3">
                      <v-col cols="12">
                        <!-- <label class="col-form-label"
                          >Current Password
                          <span class="text-danger">*</span></label
                        > -->
                        <v-text-field label="Current Password" dense outlined :hide-details="!errors.current_password"
                          :append-icon="current_password_show ? 'mdi-eye' : 'mdi-eye-off'
                            " :type="current_password_show ? 'text' : 'password'" v-model="payload.current_password"
                          class="input-group--focused" @click:append="
                            current_password_show = !current_password_show
                            " :error="errors.current_password" :error-messages="errors && errors.current_password
                              ? errors.current_password
                              : ''
                              " :disabled="!$pagePermission.can('company_edit', this)"></v-text-field>
                      </v-col>
                      <v-col md="12" sm="12" cols="12" dense>
                        <!-- <label class="col-form-label"
                          >Password <span class="text-danger">*</span></label
                        > -->
                        <v-text-field label="New Password" dense outlined :hide-details="!errors.password" :append-icon="show_password ? 'mdi-eye' : 'mdi-eye-off'
                          " :type="show_password ? 'text' : 'password'" v-model="payload.password"
                          class="input-group--focused" @click:append="show_password = !show_password"
                          :error="errors.password" :error-messages="errors && errors.password ? errors.password[0] : ''
                            " :disabled="!$pagePermission.can('company_edit', this)"></v-text-field>
                      </v-col>

                      <v-col md="12" sm="12" cols="12" dense>
                        <!-- <label class="col-form-label"
                          >Confirm Password
                          <span class="text-danger">*</span></label
                        > -->
                        <v-text-field label="Confirm New Password" dense outlined
                          :hide-details="!errors.password_confirmation" :append-icon="show_password_confirm ? 'mdi-eye' : 'mdi-eye-off'
                            " :type="show_password_confirm ? 'text' : 'password'"
                          v-model="payload.password_confirmation" class="input-group--focused" @click:append="
                            show_password_confirm = !show_password_confirm
                            " :error="errors.show_password_confirm" :error-messages="errors && errors.show_password_confirm
                              ? errors.show_password_confirm[0]
                              : ''
                              " :disabled="!$pagePermission.can('company_edit', this)"></v-text-field>
                      </v-col>
                      <v-col cols="12">
                        <div class="text-right">
                          <v-btn v-if="$pagePermission.can('company_edit', this)" dark small :loading="loading_password"
                            color="primary" @click="update_password">
                            Submit
                          </v-btn>
                        </div>
                      </v-col>
                    </v-col>
                  </v-row>
                </v-container>
              </v-tab-item>
              <!-- <v-tab-item>
                <v-card>
                  <v-card-text>
                    <v-row>
                      <v-col cols="3">
                        <div class="form-group">
                          <label class="col-form-label">Currency</label>
                          <span class="error--text">*</span>
                          <v-autocomplete outlined dense small v-model="company_payload.currency" :items="[
                            { text: 'AED', value: 'AED' },
                            { text: 'INR ₹', value: '₹' },
                            { text: 'US $', value: '$' },
                          ]">
                          </v-autocomplete>

                          <span v-if="errors && errors.currency" class="error--text mt-2">{{ errors.p_o_box_no[0]
                          }}</span>
                        </div>
                        <v-col cols="12">
                          <div class="text-right">
                            <v-btn v-if="
                              can('setting_company_change_password_access')
                            " dark small :loading="loading_password" color="primary"
                              @click="update_company_currency()">
                              Update
                            </v-btn>
                          </div>
                        </v-col>
                      </v-col>
                    </v-row>
                  </v-card-text>
                </v-card>
              </v-tab-item> -->
              <!-- <v-tab-item>
                <TaxSlabs />
              </v-tab-item>
              <v-tab-item>
                <Whatsapp />
              </v-tab-item> -->


            </v-tabs>
          </v-card>
        </v-col>
      </v-row>
    </div>
    <Preloader v-else />
  </div>
</template>

<script>
import TaxSlabs from "../../components/Admin/TaxSlabs.vue";
import NotificationSettings from "../../components/Alarm/NotificationSettings.vue";
import ParkingSettings from "../../components/Parking/ParkingSettings.vue";

import Whatsapp from "../../components/Whatsapp.vue";
import timeZones from "../../defaults/utc_time_zones.json";

export default {
  components: { Whatsapp, TaxSlabs, NotificationSettings, ParkingSettings },

  data: () => ({
    windowHeight: 600,
    contact_payload_update: {},
    business_licence_payload: {},
    timeZones,
    originalURL: process.env.APP_URL + "register/visitor/walkin/", //`https://mytime2cloud.com/register/visitor/walkin/`,
    fullCompanyLink: null,
    qrCompanyCodeDataURL: null,
    show_password_confirm: false,
    current_password_show: false,
    show_password: false,
    loading_password: false,
    menuIssueDate: false,
    menuExpiryDate: false,
    IssueDate: null,
    vertical: false,
    id: "",
    loading: false,
    preloader: true,
    upload: {
      name: "",
    },
    tableHeight: 600,
    company_payload: {
      name: "",
      logo: "",
      member_from: "",
      expiry: "",
      max_branches: "",
      max_employee: "",
      max_devices: "",
      mol_id: "",
      p_o_box_no: "",
    },

    company_trade_license: {
      license_no: "",
      license_type: "",
      emirate: "",
      makeem_no: "",
      manager: "",
      issue_date: "",
      expiry_date: "",
    },

    contact_payload: {
      name: "",
      number: "",
      position: "",
      whatsapp: "",
    },
    user_payload: {
      password: "",
      password_confirmation: "",
    },
    payload: {
      password: "",
      current_password: "",
      password_confirmation: "",
    },
    geographic_payload: {
      lat: "",
      lon: "",
      location: "",
    },
    e1: 1,
    errors: [],
    previewImage: null,
    data: {},
    response: "",
    snackbar: false,
    menu_start_date: false,
    menu_end_date: false,
  }),
  async mounted() {
    if (window) this.windowHeight = window.outerHeight;
    // this.tableHeight = window.outerHeight;
    // window.addEventListener("resize", () => {
    //   this.tableHeight = window.outerHeight;
    // });
  },
  async created() {
    try {
      this.getDataFromApi();
      if (process.env.ENVIRONMENT == "local") {
        this.originalURL = `http://${process.env.LOCAL_IP}:${process.env.LOCAL_PORT}/register/visitor/walkin/`;
      }
      this.fullCompanyLink = this.originalURL + this.$auth.user.company_id;
      //this.generateCompanyQRCode(this.fullCompanyLink);
    } catch (e) { }
  },
  methods: {
    async generateCompanyQRCode(fullLink) {
      try {
        this.qrCompanyCodeDataURL = await this.$qrcode.generate(fullLink);
      } catch (error) {
        console.error("Error generating QR code:", error);
      }
    },

    can(per) {
      return this.$pagePermission.can(per, this);
    },
    update_company_currency() {
      this.$axios
        .post(`/company/${this.$auth?.user?.company?.id}/update_currency`, {
          currency: this.company_payload.currency,
        })
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = "Currency updated successfully";
          }
        })
        .catch((e) => console.log(e));
    },
    update_password() {
      this.$axios
        .post(
          `/company/${this.$auth?.user?.company?.id}/update/user`,
          this.payload
        )
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = "Setting updated successfully";
          }
        })
        .catch((e) => console.log(e));
    },
    getTimezones() {
      return Object.keys(this.timeZones).map((key) => ({
        offset: this.timeZones[key].offset,
        time_zone: this.timeZones[key].time_zone,
        key: key,
        text:
          this.timeZones[key].time_zone +
          " - " +
          key +
          " - " +
          this.timeZones[key].offset,
      }));
    },
    getDataFromApi() {
      this.id = this.$auth.user.company_id;
      this.$axios
        .get(`company/${this.$auth.user.company_id}`)
        .then(({ data }) => {
          let r = data.record;
          this.company_payload = r;
          this.contact_payload = r.contact;
          this.contact_payload_update = r.contact;
          this.user_payload = r.user;

          if (r.trade_license) {
            this.company_trade_license = r.trade_license;
          }

          let mf = this.formatted_date(r.member_from);
          let exp = this.formatted_date(r.expiry);
          this.company_payload.member_from = mf;
          this.company_payload.expiry = exp;

          this.geographic_payload = {
            lat: this.company_payload.lat,
            lon: this.company_payload.lon,
            location: this.company_payload.location,
            utc_time_zone: this.company_payload.utc_time_zone,
          };
          this.preloader = false;
        });
    },

    formatted_date(v) {
      let [year, month, date] = v.split("/");
      return `${year}-${month}-${date}`;
    },
    onpick_attachment() {
      this.$refs.attachment_input.click();
    },

    attachment(e) {
      this.upload.name = e.target.files[0] || "";

      let input = this.$refs.attachment_input;
      let file = input.files;
      if (file && file[0]) {
        let reader = new FileReader();
        reader.onload = (e) => {
          this.previewImage = e.target.result;
        };
        reader.readAsDataURL(file[0]);
        this.$emit("input", file[0]);
      }
      this.loading = false;
    },
    cancelAttachment() {
      this.upload.name = "";
      this.previewImage = null;
    },
    update_company_business_license() {
      if (confirm("Are you sure want to Update the settings?")) {
        let payload = new FormData();

        payload.append("company_id", this.$auth.user.company_id);
        if (this.company_payload.business_license)
          payload.append(
            "business_license",
            this.company_payload.business_license
          );

        if (this.company_payload.business_license_type)
          payload.append(
            "business_license_type",
            this.company_payload.business_license_type
          );

        if (this.company_payload.business_emirati)
          payload.append(
            "business_emirati",
            this.company_payload.business_emirati
          );
        if (this.company_payload.business_license_issue_date)
          payload.append(
            "business_license_issue_date",
            this.company_payload.business_license_issue_date
          );
        if (this.company_payload.business_license_expiry_date)
          payload.append(
            "business_license_expiry_date",
            this.company_payload.business_license_expiry_date
          );
        if (this.company_payload.business_makeem_number)
          payload.append(
            "business_makeem_number",
            this.company_payload.business_makeem_number
          );

        this.start_process(
          `/company/${this.id}/update_business_license`,
          payload,
          `Company`
        );
      }
    },
    update_company() {
      // this.update_contact();
      // this.update_geographic();

      let payload = new FormData();

      payload.append("name", this.company_payload.name);
      if (this.upload.name) {
        payload.append("logo", this.upload.name);
        //payload.append("logo_only", 1);
      }
      payload.append("location", this.company_payload.location);
      payload.append("member_from", this.company_payload.member_from);
      payload.append("expiry", this.company_payload.expiry);
      payload.append("max_employee", this.company_payload.max_employee);
      payload.append("max_branches", this.company_payload.max_branches);
      payload.append("max_devices", this.company_payload.max_devices);

      payload.append("mol_id", this.company_payload.mol_id);
      payload.append("p_o_box_no", this.company_payload.p_o_box_no);

      this.start_process(`/company/${this.id}/update`, payload, `Company`);
      this.loading = false;
    },
    update_contact() {
      // let payload = new FormData();

      // if (this.contact_payload_update.contact_person_name)
      //   payload.append("name", this.contact_payload_update.contact_person_name);
      // if (this.contact_payload_update.contact_person_number)
      //   payload.append(
      //     "number",
      //     this.contact_payload_update.contact_person_number
      //   );
      // if (this.contact_payload_update.contact_person_position)
      //   payload.append(
      //     "position",
      //     this.contact_payload_update.contact_person_position
      //   );
      // if (this.contact_payload_update.contact_person_whatsapp)
      //   payload.append(
      //     "whatsapp",
      //     this.contact_payload_update.contact_person_whatsapp
      //   );

      this.start_process(
        `/company/${this.id}/update/contact`,
        this.contact_payload_update,
        `Contact`
      );
    },

    update_license() {
      this.start_process(
        `/company/${this.id}/trade-license`,
        this.company_trade_license,
        `Trade License`
      );
    },
    update_geographic() {
      this.start_process(
        `/company/${this.id}/update/geographic`,
        this.geographic_payload,
        `Geographic Info`
      );
    },
    update_user() {
      this.start_process(
        `/company/${this.id}/update/user`,
        this.user_payload,
        `User`
      );
    },
    start_process(url, payload, model) {
      this.loading = true;

      this.$axios
        .post(url, payload)
        .then(({ data }) => {
          this.loading = false;

          if (!data.status) {
            this.errors = data.errors;
          } else {
            this.snackbar = true;
            this.response = model + " updated successfully";

            this.upload.name = "";
            this.errors = [];
            this.loading = false;
          }
        })
        .catch((e) => console.log(e));
    },
  },
};
</script>
