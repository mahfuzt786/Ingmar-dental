// Status options for teeth
export const STATUS_OPTIONS = [
  {value: "", text:"Nicht Behandlungswürdiger Zahn"},
  {value: "a",    text:"Anker Adhäsivbrücke"},
  {value: "ab",   text:"Adhäsivbrückenglied"},
  {value: "b",    text:"Brückenglied"},
  {value: "e",    text:"Ersetzter Zahn: bitte 'ew' eintragen für Neuversorgung"},
  {value: "f",    text:"Fehlender Zahn"},
  {value: "i-",   text:"Implantat ohne Suprakonstruktion"},
  {value: "ik",   text:"Implantat mit intakter Suprakonstruktion"},
  {value: "k",    text:"Klinisch intakte Krone"},
  {value: "pk",   text:"Teilkrone"},
  {value: "r",    text:"Wurzelstiftkappe"},
  {value: "t",    text:"Klinisch intakte Teleskopkrone"},
  {value: ")(",   text:"Lückenschluss"},
];


// Findings options for teeth
export const FINDINGS_OPTIONS = {
  "": [
    {value: "pw",   text:"erhaltungswürdiger Zahn mit partiellen Substanzdefekten"},
    {value: "ww",   text:"erhaltungswürdiger Zahn mit weitgehender Zerstörung"},
    {value: "ur",   text:"unzureichende Retention"},
    {value: "x",    text:"nicht erhaltungswürdiger Zahn"},
  ],
  "r": [
    {value: "rw",   text:"Erneuerungsbedürftige Wurzelstiftkappe"},
    {value: "ww",   text:"erhaltungswürdiger Zahn mit weitgehender Zerstörung"},
    {value: "x",    text:"nicht erhaltungswürdiger Zahn"},
  ],
  "k": [
    {value: "kw",   text:"erneuerungsbedürftige Krone"},
    {value: "x",    text:"nicht erhaltungswürdiger Zahn"},
  ],
  "pk": [
    {value: "pw",   text:"erhaltungswürdiger Zahn mit partiellen Substanzdefekten"},
    {value: "ww",   text:"erhaltungswürdiger Zahn mit weitgehender Zerstörung"},
    {value: "x",    text:"nicht erhaltungswürdiger Zahn"},
  ],
  "a": [
    {value: "aw",   text:"erneuerungsbedürftiger Anker Adhäsivbrücke"},
    {value: "pw",   text:"erhaltungswürdiger Zahn mit partiellen Substanzdefekten"},
    {value: "ww",   text:"erhaltungswürdiger Zahn mit weitgehender Zerstörung"},
    {value: "x",    text:"nicht erhaltungswürdiger Zahn"},
  ],
  "ab": [
    {value: "abw",  text:"erneuerungsbedürftiges Adhäsivbrückenglied"},
  ],
  "b": [
    {value: "bw",    text:"Missing description"},
  ],
  "e": [
    {value: "ew",   text:"ersetzter, aber erneuerungsbedürftiger Zahn"},
  ],
  "ik": [
    {value: "sw",   text:"Erneuerungsbedürftige Suprakonstruktion"},
    {value: "ix",   text:"zu entfernendes Implantat"},
  ],
  "i-": [
    {value: "ix",   text:"zu entfernendes Implantat"},
  ],
  "t": [
    {value: "tw",   text:"erneuerungsbedürftiges Teleskop"},
    {value: "ww",   text:"erhaltungswürdiger Zahn mit weitgehender Zerstörung"},
    {value: "x",    text:"nicht erhaltungswürdiger Zahn"},
  ],
  ")(": [
  ],
  "f": [
    {value: "fi",   text:"fehlender Zahn - geplante Implantat-Erstversorgung"},
  ],
};

// Teeth numbers
export const TOP_TEETH = [18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28];
export const BOTTOM_TEETH  = [48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38];
