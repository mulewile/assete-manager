export default async function getData(URL, action_type) {
  const response = await fetch(URL, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      action: action_type,
    },
  });

  try {
    if (response.ok) {
      const respons_data = await response.json();
      displayDatabaseConnectionMessage(respons_data);
      return respons_data;
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
