// store/dashboard.js

export const state = () => ({
  tabStore: {},
});

export const mutations = {
  RESET_STATE(state) {
    Object.keys(state).forEach((key) => {
      state[key] = null;
    });
  },
  settabStore(state, tabStore) {
    state.tabStore = tabStore;
  },
};

export const actions = {
  resetState({ commit }) {
    commit("RESET_STATE");
  },
};
