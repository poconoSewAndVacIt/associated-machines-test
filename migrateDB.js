const pool = require("./dbConnection");
const {
  parseMachineTagsGroups,
  parsePartAssociatedTagsGroups,
} = require("./helpers");
const { saveJson } = require("./jsonHelper");

const { makeAllProductsQueryWithLimit } = require("./queryFunctions");

const LIMIT = 100000;

run();

// grab all parts and products

// find all unique instances of something in the part/product

async function run() {
  const connection = await pool.getConnection();
  const query = makeAllProductsQueryWithLimit(LIMIT);
  const data = await connection.query(query);

  // console.log(data.length);
  // console.log(data);

  let parts = [];
  let machines = [];
  let other = [];

  let partCount = 0,
    partTagCount = 0,
    machineCount = 0,
    machineTagCount = 0,
    otherCount = 0;

  for (let item of data) {
    if (item.TV13_VALUE) {
      machines.push(item);
    } else if (item.TV8_VALUE) {
      parts.push(item);
    } else {
      other.push(item);
    }
  }

  console.log("other", other.length);

  //   Machine Tags
  const machineTagSet = new Set();

  for (let m of machines) {
    // console.log(m);
    const tags = parseMachineTagsGroups(m.TV13_VALUE);
    for (let tag of tags) {
      machineTagCount++;
      machineTagSet.add(tag);
    }
  }
  //   console.log(machineTagSet);

  //   Part Tags
  const partTagSet = new Set();

  for (let p of parts) {
    // console.log(p);
    const tags = parseMachineTagsGroups(p.TV8_VALUE);
    for (let tag of tags) {
      partTagCount++;
      partTagSet.add(tag);
    }
  }
  //   console.log(partTagSet);

  console.log("machineTagCount", machineTagCount);
  console.log("partTagCount", partTagCount);

  const partTagArr = [...partTagSet].sort();
  const machineTagArr = [...machineTagSet].sort();

  saveJson("./tags/allTags.json", {
    machineTagSet: [...machineTagArr],
    partTagSet: [...partTagArr],
  });

  // // for machines
  //     if (isMachine) {
  //       // Find matching parts
  //       const tagsAndGroups = item.machine_tags_groups
  //         .replaceAll(/\\r|\\n/g, "")
  //         .replaceAll("Associated Machines", "")
  //         .replaceAll(/[%\|=]/g, "")
  //         .split(";")
  //         .map((item) => item.trim())
  //         .filter((item) => item.length);

  // // for parts
  //     } else if (isPart) {
  //       // Find matching matchines
  //       const tagsAndGroups = item.associated_tags_groups
  //         .replaceAll(/\\r|\\n/g, "")
  //         .split(";")
  //         .map((item) => item.trim())
  //         .filter((item) => item.length);
  //       console.log(tagsAndGroups);
  await pool.end();
  process.exit();
}
