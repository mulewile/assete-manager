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
      console.log("Response", response_data);
    } else {
      console.error("Something went wrong:", response.statusText);
    }
  } catch (error) {
    console.error("Error from the server:", error);
  }
}
