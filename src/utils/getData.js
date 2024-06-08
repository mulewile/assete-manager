import {
  loginFormContainerElement,
  appHeaderElement,
  hardwareTableContainerElement,
  hardwareTableBodyElement,
  hardwareTableElement,
} from "../index.js";
import { create_table_body } from "./utils.js";

export default async function getData(URL, action_type) {
  const response = await fetch(URL, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ action_type }),
  });

  try {
    if (response.ok) {
      const response_data = await response.json();
      console.log("response_data", response_data);

      if (response_data.is_logged_in === true) {
        appHeaderElement.classList.remove("hidden");
        hardwareTableContainerElement.classList.remove("hidden");
        const hardware_data = response_data.all_hardware_data;
        create_table_body(
          hardware_data,
          hardwareTableBodyElement,
          hardwareTableElement
        );
        console.log("hardware data response", hardware_data);
      } else if (response_data.is_logged_in !== true) {
        loginFormContainerElement.classList.remove("hidden");
      }

      return response_data;
    } else {
      throw new Error("Something went wrong");
    }
  } catch (error) {
    console.error(error);
  }
}

//The function displayDatabaseConnectionMessage is used to display the message on the screen.
export function displayDatabaseConnectionMessage({
  connection_status,
  database,
}) {
  const messageDiv = document.createElement("div");
  messageDiv.textContent = connection_status + " to " + database + " database";
  messageDiv.classList.add("message");
  document.body.appendChild(messageDiv);
  settimeout(() => {
    messageDiv.remove();
  }, 3000);
}

//settimeout resusable function
export function settimeout(callback, time) {
  setTimeout(() => {
    callback();
  }, time);
}
