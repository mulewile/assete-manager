import getData from "./utils/getData.js";

document.addEventListener("DOMContentLoaded", (event) => {
  console.log("DOM fully loaded and parsed");
  const GET_DATA_URL = "/connect.php";
  const action_type = "get_data";
  getData(GET_DATA_URL, action_type);
});
