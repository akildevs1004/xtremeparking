// store/dashboard.js

export const state = () => ({
  BuildingTypes: null,
  AddressTypes: null,
  DeviceTypes: null,
  DeviceModels: null,

  SensorTypes: null,
  ZoneTypes: null,
  AlarmTypeNotificationIcons: null,
});

export const mutations = {
  RESET_STATE(state) {
    Object.keys(state).forEach((key) => {
      state[key] = null;
    });
  },

  BuildingTypes(state, data) {
    state.BuildingTypes = data;
  },
  AddressTypes(state, data) {
    state.AddressTypes = data;
  },
  DeviceTypes(state, data) {
    state.DeviceTypes = data;
  },
  SensorTypes(state, data) {
    state.SensorTypes = data;
  },
  ZoneTypes(state, data) {
    state.ZoneTypes = data;
  },
  DeviceModels(state, data) {
    state.DeviceModels = data;
  },
  AlarmTypeNotificationIcons(state, data) {
    state.AlarmTypeNotificationIcons = data;
  },
};

export const actions = {
  resetState({ commit }) {
    commit("RESET_STATE");
  },
};
