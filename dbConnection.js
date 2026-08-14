const mariadb = require("mariadb");
require("dotenv").config();

console.log(process.env.LOCAL_DB_HOST);
console.log(process.env.LOCAL_DB_USER);
console.log(process.env.LOCAL_DB_PASSWORD);
console.log(process.env.LOCAL_DB_NAME);
console.log(process.env.LOCAL_DB_PORT);

// Create a configured connection pool
const pool = mariadb.createPool({
  host: process.env.LOCAL_DB_HOST, // Your database host address
  user: process.env.LOCAL_DB_USER, // Your MariaDB username
  password: process.env.LOCAL_DB_PASSWORD, // Your MariaDB password
  database: process.env.LOCAL_DB_NAME,
  port: process.env.LOCAL_DB_PORT, // Default MariaDB port
  connectionLimit: 5, // Maximum simultaneous connections
});

// Export the pool to use it across your application
module.exports = pool;
