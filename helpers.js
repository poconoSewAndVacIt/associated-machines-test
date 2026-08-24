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

module.exports = { parseMachineTagsGroups, parsePartAssociatedTagsGroups };
