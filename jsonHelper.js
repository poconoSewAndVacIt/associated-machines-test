const fs = require("fs");
const path = require("path");

/**
 * Saves a JavaScript object as a formatted JSON file.
 * @param {string} name - The name of the file (e.g., 'config' or 'user_data').
 * @param {Object} object - The JavaScript object or array to save.
 * @returns {Promise<void>}
 */
function saveJson(name, object) {
  // Ensures the file extension is .json
  const filename = fixJsonFileName(name);
  const filePath = path.resolve(filename);

  // Converts object to readable JSON string (2-space indentation)
  const data = JSON.stringify(object, null, 2);

  fs.writeFileSync(filePath, data, "utf8");
}

/**
 * Loads and parses a JSON file back into a JavaScript object.
 * @param {string} name - The name of the file to load (e.g., 'config').
 * @returns {Promise<Object|null>} The parsed object, or null if the file does not exist.
 */
function loadJson(name) {
  const filename = fixJsonFileName(name);
  const filePath = path.resolve(filename);

  try {
    const data = fs.readFileSync(filePath, "utf8");
    return JSON.parse(data);
  } catch (error) {
    // Returns null gracefully if the file isn't found
    if (error.code === "ENOENT") {
      return null;
    }
    throw error;
  }
}

// Export the functions using CommonJS syntax
module.exports = {
  saveJson,
  loadJson,
};

function fixJsonFileName(name) {
  return name.endsWith(".json") ? name : `${name}.json`;
}
