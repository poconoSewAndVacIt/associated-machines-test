const swaggerAutogen = require("swagger-autogen");

const doc = {
  info: {
    title: "My Express API",
    description: "Automatically generated Swagger documentation.",
  },
  host: "localhost:3000",
};

const outputFile = "./swagger-output.json";
const routesFiles = ["./server.js"];

swaggerAutogen(outputFile, routesFiles, doc);
