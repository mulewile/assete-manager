import {
  createUserFormContainerElement,
  loginFormContainerElement,
  appHeaderElement,
} from "../index.js";

//This function is used to post data to the server
//Paramaters: URL, action_type, data

export default async function postData(URL, action_type, post_data) {
  const response = await fetch(URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(Object.assign({}, post_data, { action_type })),
  });

  try {
    if (response.ok) {
      const response_data = await response.json();

      console.log("response_data", response_data);
      if (response_data.isSignedUp === true) {
        //create own function for this
        createUserFormContainerElement.reset();
        createUserFormContainerElement.classList.add("hidden");
      } else if (response_data.is_logged_in === true) {
        loginFormContainerElement.classList.add("hidden");
        appHeaderElement.classList.remove("hidden");
        console.log("User is logged in");
      } else if (response_data.is_logged_in === false) {
        loginFormContainerElement.classList.remove("hidden");
        appHeaderElement.classList.add("hidden");
        console.log("User is not logged in");
      }
    } else {
      console.error("Something went wrong:", response.statusText);
    }
  } catch (error) {
    console.error("Error from the server:", error);
  }
}
