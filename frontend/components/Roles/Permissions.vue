<template>
  <div style="max-height: 450px; overflow-y: auto">
    <div class="text-right">
      <v-btn-toggle v-model="toggle_exclusive">
        <v-btn text small dense @click="all"> Expand All </v-btn>
        <v-btn text small dense @click="none">Close All </v-btn></v-btn-toggle
      >
    </div>

    <v-expansion-panels class="roles" v-model="panel" multiple focusable>
      <v-expansion-panel v-for="(menu, index) in topMenus" :key="index">
        <v-expansion-panel-header>{{ menu.label }}</v-expansion-panel-header>
        <v-expansion-panel-content>
          <table>
            <tr>
              <td style="width: 30%" class="border-bottom">
                <div class="text-color"></div>
              </td>
              <!-- <td class="border-bottom">
                <div class="text-color text-center">Access</div>
              </td> -->
              <td class="border-bottom">
                <div class="text-color text-center">View</div>
              </td>
              <td class="border-bottom">
                <div class="text-color text-center">Create</div>
              </td>
              <td class="border-bottom">
                <div class="text-color text-center">Edit</div>
              </td>
              <td class="border-bottom">
                <div class="text-color text-center">Delete</div>
              </td>
            </tr>
            <tr v-if="filteredMenus(menu.name).length > 1">
              <td style="width: 30%" class="border-bottom">
                <div class="text-color">
                  <b>Select All </b>
                </div>
              </td>
              <!-- <td class="border-bottom">
                <div
                  class="text-color text-center d-flex align-center justify-center"
                >
                  <v-checkbox
                    class="py-1 pl-1 ma-0"
                    color="primary"
                    dense
                    hide-details
                    @change="
                      (isChecked) =>
                        selectAllByfilteredMenus('access', menu.name, isChecked)
                    "
                  ></v-checkbox>
                </div>
              </td> -->
              <td class="border-bottom">
                <div
                  class="text-color text-center d-flex align-center justify-center"
                >
                  <v-checkbox
                    class="py-1 pl-1 ma-0"
                    color="success"
                    dense
                    hide-details
                    @change="
                      (isChecked) =>
                        selectAllByfilteredMenus('view', menu.name, isChecked)
                    "
                  ></v-checkbox>
                </div>
              </td>
              <td class="border-bottom">
                <div
                  class="text-color text-center d-flex align-center justify-center"
                >
                  <v-checkbox
                    class="py-1 pl-1 ma-0"
                    color="info"
                    dense
                    hide-details
                    @change="
                      (isChecked) =>
                        selectAllByfilteredMenus('create', menu.name, isChecked)
                    "
                  ></v-checkbox>
                </div>
              </td>
              <td class="border-bottom">
                <div
                  class="text-color text-center d-flex align-center justify-center"
                >
                  <v-checkbox
                    class="py-1 pl-1 ma-0"
                    color="warning"
                    dense
                    hide-details
                    @change="
                      (isChecked) =>
                        selectAllByfilteredMenus('edit', menu.name, isChecked)
                    "
                  ></v-checkbox>
                </div>
              </td>
              <td class="border-bottom">
                <div
                  class="text-color text-center d-flex align-center justify-center"
                >
                  <v-checkbox
                    class="py-1 pl-1 ma-0"
                    color="red"
                    dense
                    hide-details
                    @change="
                      (isChecked) =>
                        selectAllByfilteredMenus('delete', menu.name, isChecked)
                    "
                  ></v-checkbox>
                </div>
              </td>
            </tr>
            <tr v-for="(childMenu, idx) in filteredMenus(menu.name)" :key="idx">
              <td style="width: 30%" class="border-bottom">
                <div class="text-color">
                  {{ childMenu.title }}
                </div>
              </td>
              <!-- <td class="border-bottom">
                <div class="text-center d-flex align-center justify-center">
                  <v-checkbox
                    class="py-1 pl-1 ma-0"
                    color="primary"
                    :value="childMenu.page_name + '_access'"
                    v-model="permission_pages"
                    dense
                    hide-details
                  >
                  </v-checkbox>
                </div>
              </td> -->
              <td class="border-bottom">
                <div class="text-center d-flex align-center justify-center">
                  <v-checkbox
                    class="py-1 pl-1 ma-0"
                    color="success"
                    :value="childMenu.page_name + '_view'"
                    v-model="permission_pages"
                    dense
                    hide-details
                  >
                  </v-checkbox>
                </div>
              </td>
              <td class="border-bottom">
                <div class="text-center d-flex align-center justify-center">
                  <v-checkbox
                    class="py-1 pl-1 ma-0"
                    color="info"
                    :value="childMenu.page_name + '_create'"
                    v-model="permission_pages"
                    dense
                    hide-details
                  >
                  </v-checkbox>
                </div>
              </td>
              <td class="border-bottom">
                <div class="text-center d-flex align-center justify-center">
                  <v-checkbox
                    class="py-1 pl-1 ma-0"
                    color="warning"
                    :value="childMenu.page_name + '_edit'"
                    v-model="permission_pages"
                    dense
                    hide-details
                  >
                  </v-checkbox>
                </div>
              </td>
              <td class="border-bottom">
                <div class="text-center d-flex align-center justify-center">
                  <v-checkbox
                    color="red"
                    class="py-1 pl-1 ma-0"
                    :value="childMenu.page_name + '_delete'"
                    v-model="permission_pages"
                    dense
                    hide-details
                  >
                  </v-checkbox>
                </div>
              </td>
            </tr>
          </table>
        </v-expansion-panel-content>
      </v-expansion-panel>
    </v-expansion-panels>
  </div>
</template>

<script>
export default {
  props: ["permissions", "defaultPermissionsIds", "menuList"],
  data() {
    return {
      panel: [],
      expandedIndex: null, // Index of the currently expanded item
      permission_pages: [],
      toggle_exclusive: 0,
      topMenus: [],
      defaultPermissions: ["view", "edit", "delete"],
      menus: [],
    };
  },
  created() {
    if (this.menuList) {
      this.topMenus = this.menuList.topmenu;
      this.menus = this.menuList.submenu;
    }
  },
  mounted() {
    this.permission_pages = { ...this.defaultPermissionsIds };
    // this.all();
    //this.permission_pages = this.defaultPermissionsIds;
  },
  watch: {
    defaultPermissionsIds(newVal) {
      this.permission_pages = newVal;
    },
    permission_pages() {
      this.$emit("selectedPermissions", this.permission_pages);
    },
  },

  methods: {
    all() {
      this.panel = [...Array(this.topMenus.length).keys()].map((k, i) => i);
    },
    // Reset the panel
    none() {
      this.panel = [];
    },
    toggleExpand(index) {
      // If the index is already expanded, collapse it; otherwise, expand it
      this.expandedIndex = this.expandedIndex === index ? null : index;
    },
    filteredPermissions(module) {
      return this.permissions[module.toLocaleLowerCase()];
    },
    filteredMenus(topMenuName) {
      return this.menus.filter((menu) => menu.topMenu === topMenuName);
    },
    selectAllByfilteredMenus(action, topMenuName, isChecked) {
      const actionLower1 = action.toLowerCase();

      // Initialize permission_pages as an array if it’s not already
      if (!Array.isArray(this.permission_pages)) {
        this.permission_pages = [];
      }

      // Filter menus by topMenuName and map them to formatted permission strings
      const permissions = this.menus
        .filter((menu) => menu.topMenu === topMenuName)
        .map((menu) => `${menu.page_name}_${actionLower1}`);

      if (isChecked) {
        // Add only unique permissions that aren't already in permission_pages
        this.permission_pages = [
          ...new Set([...this.permission_pages, ...permissions]),
        ];
      } else {
        // Remove permissions that match values in permissions array
        this.permission_pages = this.permission_pages.filter(
          (page) => !permissions.includes(page)
        );
      }

      return false;
    },
  },
};
</script>
