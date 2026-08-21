const jsonHelper = require("./jsonHelper");

let sample = { haha: 1 };

let fileName = "./junk/sample.json";

jsonHelper.saveJson(fileName, sample);

let loadedSample = jsonHelper.loadJson(fileName);

console.log("loadedSample", loadedSample);
