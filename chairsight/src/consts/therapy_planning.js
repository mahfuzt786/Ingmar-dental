// NORMAL_GOZ OPTIONS
export const NORMAL_GOZ_OPTIONS = [
  { value: null, text: "F." },
  { value: "1.8", text: "1.8" },
  { value: "1.9", text: "1.9" },
  { value: "2.0", text: "2.0" },
  { value: "2.1", text: "2.1" },
  { value: "2.2", text: "2.2" },
  { value: "2.3", text: "2.3" },
  { value: "2.4", text: "2.4" },
  { value: "2.5", text: "2.5" },
  { value: "2.6", text: "2.6" },
  { value: "2.7", text: "2.7" },
  { value: "2.8", text: "2.8" },
  { value: "2.9", text: "2.9" },
  { value: "3.0", text: "3.0" },
  { value: "3.1", text: "3.1" },
  { value: "3.2", text: "3.2" },
  { value: "3.3", text: "3.3" },
  { value: "3.4", text: "3.4" },
  { value: "3.5", text: "3.5" }
];

// NORMAL_GOZ OPTIONS
export const OPTIONAL_GOZ_OPTIONS = [
  { value: null, text: "F." },
  { value: "0.0", text: "Not used" },
  { value: "1.8", text: "1.8" },
  { value: "1.9", text: "1.9" },
  { value: "2.0", text: "2.0" },
  { value: "2.1", text: "2.1" },
  { value: "2.2", text: "2.2" },
  { value: "2.3", text: "2.3" },
  { value: "2.4", text: "2.4" },
  { value: "2.5", text: "2.5" },
  { value: "2.6", text: "2.6" },
  { value: "2.7", text: "2.7" },
  { value: "2.8", text: "2.8" },
  { value: "2.9", text: "2.9" },
  { value: "3.0", text: "3.0" },
  { value: "3.1", text: "3.1" },
  { value: "3.2", text: "3.2" },
  { value: "3.3", text: "3.3" },
  { value: "3.4", text: "3.4" },
  { value: "3.5", text: "3.5" }
];

export const MOCK_GENERAL_PLANNING = {
  id: "b5fc39a2-8ea8-4ada-a498-5c6f0e709a0e",
  status: "not_started",
  tree: {
    id: "7c3b497c-541e-44cb-ac14-7872fc308557",
    regions: [
      {
        label: "43",
        questions: [
          {
            text: "Behandlung",
            depends_on_option: null,
            options: [
              {
                id: "86772712-31cb-4d21-96de-0cd4a8912475",
                answer: "Krone",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2197",
                      name: "Adhäsive Befestigung",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  },
                  {
                    goz: {
                      code: "2180",
                      name: "Adhäsiver Aufbau",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: ["partial-crown"],
                  rules: []
                }
              }
            ]
          }
        ]
      },
      {
        label: "11-13",
        questions: [
          {
            text: "Behandlung",
            depends_on_option: null,
            options: [
              {
                id: "86772712-31cb-4d21-96de-0cd4a8912475",
                answer: "Krone",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2197",
                      name: "Adhäsive Befestigung",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  },
                  {
                    goz: {
                      code: "2180",
                      name: "Adhäsiver Aufbau",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: ["partial-crown"],
                  rules: []
                }
              }
            ]
          },
          {
            text: "Gerüstwahl",
            depends_on_option: "4476c44e-8da5-44c0-9251-76a9d123652d",
            options: [
              {
                id: "15ced41e-1845-426b-bda9-84e1530bd256",
                answer: "IPS e.max",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2220",
                      name: "Teilkrone",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              },
              {
                id: "2bc8bb95-0b7f-4987-a248-a1bc37c2e2ae",
                answer: "ZrO2 unverblendet",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2220",
                      name: "Teilkrone",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              },
              {
                id: "33e3be3a-94b8-442c-9392-457bc3d9d19d",
                answer: "NEM unverblendet",
                therapy_code: null,
                manufacturing: null,
                bema: {
                  code: "20c",
                  name: "metallische Teilkrone"
                },
                goz: [],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              }
            ]
          }
        ]
      },
      {
        label: "Upper Jaw",
        questions: [
          {
            text: "Methode",
            depends_on_option: "86772712-31cb-4d21-96de-0cd4a8912475",
            options: [
              {
                id: "63259f32-bb24-4ef6-9dab-94124cb2798e",
                answer: "CEREC",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2220",
                      name: "Teilkrone",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  },
                  {
                    goz: {
                      code: "0065",
                      name: "Optisch-elektronische Abformung",
                      standard_factor: "2.3"
                    },
                    factor: "2.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: ["cerec"],
                  rules: ["tag:partial-crown"]
                }
              },
              {
                id: "4476c44e-8da5-44c0-9251-76a9d123652d",
                answer: "konventionell",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [],
                materials: [],
                selected: false,
                debugger: {
                  tags: ["conventional"],
                  rules: ["tag:partial-crown"]
                }
              }
            ]
          },
          {
            text: "Gerüstwahl",
            depends_on_option: "4476c44e-8da5-44c0-9251-76a9d123652d",
            options: [
              {
                id: "15ced41e-1845-426b-bda9-84e1530bd256",
                answer: "IPS e.max",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2220",
                      name: "Teilkrone",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              },
              {
                id: "2bc8bb95-0b7f-4987-a248-a1bc37c2e2ae",
                answer: "ZrO2 unverblendet",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2220",
                      name: "Teilkrone",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              },
              {
                id: "33e3be3a-94b8-442c-9392-457bc3d9d19d",
                answer: "NEM unverblendet",
                therapy_code: null,
                manufacturing: null,
                bema: {
                  code: "20c",
                  name: "metallische Teilkrone"
                },
                goz: [],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              }
            ]
          }
        ]
      },
      {
        label: "Mandible",
        questions: [
          {
            text: "Methode",
            depends_on_option: "86772712-31cb-4d21-96de-0cd4a8912475",
            options: [
              {
                id: "63259f32-bb24-4ef6-9dab-94124cb2798e",
                answer: "CEREC",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2220",
                      name: "Teilkrone",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  },
                  {
                    goz: {
                      code: "0065",
                      name: "Optisch-elektronische Abformung",
                      standard_factor: "2.3"
                    },
                    factor: "2.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: ["cerec"],
                  rules: ["tag:partial-crown"]
                }
              },
              {
                id: "4476c44e-8da5-44c0-9251-76a9d123652d",
                answer: "konventionell",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [],
                materials: [],
                selected: false,
                debugger: {
                  tags: ["conventional"],
                  rules: ["tag:partial-crown"]
                }
              }
            ]
          },
          {
            text: "Gerüstwahl",
            depends_on_option: "4476c44e-8da5-44c0-9251-76a9d123652d",
            options: [
              {
                id: "15ced41e-1845-426b-bda9-84e1530bd256",
                answer: "IPS e.max",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2220",
                      name: "Teilkrone",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              },
              {
                id: "2bc8bb95-0b7f-4987-a248-a1bc37c2e2ae",
                answer: "ZrO2 unverblendet",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2220",
                      name: "Teilkrone",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              },
              {
                id: "33e3be3a-94b8-442c-9392-457bc3d9d19d",
                answer: "NEM unverblendet",
                therapy_code: null,
                manufacturing: null,
                bema: {
                  code: "20c",
                  name: "metallische Teilkrone"
                },
                goz: [],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              }
            ]
          }
        ]
      },
      {
        label: "Therapy",
        questions: [
          {
            text: "Behandlung",
            depends_on_option: null,
            options: [
              {
                id: "86772712-31cb-4d21-96de-0cd4a8912475",
                answer: "Krone",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2197",
                      name: "Adhäsive Befestigung",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  },
                  {
                    goz: {
                      code: "2180",
                      name: "Adhäsiver Aufbau",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: ["partial-crown"],
                  rules: []
                }
              }
            ]
          },
          {
            text: "Gerüstwahl",
            depends_on_option: "4476c44e-8da5-44c0-9251-76a9d123652d",
            options: [
              {
                id: "15ced41e-1845-426b-bda9-84e1530bd256",
                answer: "IPS e.max",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2220",
                      name: "Teilkrone",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              },
              {
                id: "2bc8bb95-0b7f-4987-a248-a1bc37c2e2ae",
                answer: "ZrO2 unverblendet",
                therapy_code: null,
                manufacturing: null,
                bema: null,
                goz: [
                  {
                    goz: {
                      code: "2220",
                      name: "Teilkrone",
                      standard_factor: "2.3"
                    },
                    factor: "1.0",
                    selected_factor: null
                  }
                ],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              },
              {
                id: "33e3be3a-94b8-442c-9392-457bc3d9d19d",
                answer: "NEM unverblendet",
                therapy_code: null,
                manufacturing: null,
                bema: {
                  code: "20c",
                  name: "metallische Teilkrone"
                },
                goz: [],
                materials: [],
                selected: false,
                debugger: {
                  tags: [null],
                  rules: ["tag:conventional"]
                }
              }
            ]
          }
        ]
      }
    ]
  }
};
