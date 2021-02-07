import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
  },
  mutations: {
  },
  actions: {
    calculateValuesApi(state, input) {
      console.log(input)
      console.log('values', JSON.stringify(input))
      const formData = new FormData();
      formData.append('data', JSON.stringify(input));
      // return axios.get(`https://www.alegralabs.com/syed/dental-api/?input=${input}&teeth_input[]=${input[0][18]}&teeth_input[]=${input[1][17]}&teeth_input[]=${input[2][16]}&teeth_input[]=${input[3][15]}&teeth_input[]=${input[4][14]}&teeth_input[]=${input[5][13]}&teeth_input[]=${input[6][12]}&teeth_input[]=${input[7][11]}&teeth_input[]=${input[8][21]}&teeth_input[]=${input[9][22]}&teeth_input[]=${input[10][23]}&teeth_input[]=${input[11][24]}&teeth_input[]=${input[12][25]}&teeth_input[]=${input[13][26]}&teeth_input[]=${input[14][27]}&teeth_input[]=${input[15][28]}&teeth_input[]=${input[16][48]}&teeth_input[]=${input[17][47]}&teeth_input[]=${input[18][46]}&teeth_input[]=${input[19][45]}&teeth_input[]=${input[20][44]}&teeth_input[]=${input[21][43]}&teeth_input[]=${input[22][42]}&teeth_input[]=${input[23][41]}&teeth_input[]=${input[24][31]}&teeth_input[]=${input[25][32]}&teeth_input[]=${input[26][33]}&teeth_input[]=${input[27][34]}&teeth_input[]=${input[28][35]}&teeth_input[]=${input[29][36]}&teeth_input[]=${input[30][37]}&teeth_input[]=${input[31][38]}`,)
      return axios.post('http://localhost/dental-api/', formData)
      // return axios.post('https://www.alegralabs.com/syed/dental-api/', formData)
      // return axios.post('http://localhost/dental-api/', {data: JSON.stringify(input)})


      // return axios.post('https://www.alegralabs.com/syed/dental-api/', {data: JSON.stringify(input)})
      // return {}
    }
  },
  modules: {
  }
})
