function parseMachineTagsGroups(tagGroup) {
  return tagGroup
    .replaceAll(/\r|\n/g, "")
    .replaceAll("Associated Machines", "")
    .replaceAll(/[%\|=]/g, "")
    .split(";")
    .map((item) => item.trim())
    .filter((item) => item.length);
}

function parsePartAssociatedTagsGroups(tagGroup) {
  return tagGroup
    .replaceAll(/\\r|\\n/g, "")
    .split(";")
    .map((item) => item.trim())
    .filter((item) => item.length);
}

function printAllRoutes(app) {
  console.log("All Routes:");
  for (let d of app.router.stack) {
    if (d.route) {
      // Routes registered directly on the app (e.g., app.get('/users'))
      const methods = Object.keys(d.route.methods).join(", ").toUpperCase();
      console.log(`[${methods}] ${d.route.path}`);
    } else if (d.name === "router") {
      // Routes registered via express.Router()
      d.handle.stack.forEach((handler) => {
        if (handler.route) {
          const methods = Object.keys(handler.route.methods)
            .join(", ")
            .toUpperCase();
          // Combines the router base path with the route path
          const basePath = d.regexp.source
            .replace(/\\\//g, "/")
            .replace(/\?\:\(\?\=\\\/\|\$\)/g, "");
          console.log(`[${methods}] ${basePath}${handler.route.path}`);
        }
      });
    }
  }
}

module.exports = {
  parseMachineTagsGroups,
  parsePartAssociatedTagsGroups,
  printAllRoutes,
};
