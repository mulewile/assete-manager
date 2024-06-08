import getData from "./utils/getData.js";
import postData from "./utils/postData.js";
import getDOMElement from "./utils/getDOMElement.js";
import resetFormFields from "./utils/resetFormFields.js";

export const createUserFormElement = getDOMElement("createUserForm");
export const loginFormElement = getDOMElement("loginForm");
export const loginFormContainerElement = getDOMElement("loginContainer");
export const createUserFormContainerElement = getDOMElement(
  "createUserFormContainer"
);
export const appHeaderElement = getDOMElement("appHeader");
export const logOutUserButtonElement = getDOMElement("logOutUserButton");
export const hardwareTableContainerElement = getDOMElement(
  "hardwareTableContainer"
);

document.addEventListener("DOMContentLoaded", async (event) => {
  console.info("DOM fully loaded and parsed");

  const GET_DATA_URL = "/connect.php";
  const action_type = "get_data";
  await getData(GET_DATA_URL, action_type);
});

//This event listener listens input events of input fields
document.addEventListener("input", (event) => {
  const EVENT_TARGET = event.target;
  const EVENT_TARGET_VALUE = EVENT_TARGET.value;

  if (EVENT_TARGET.dataset.js === "firstNameSignUp") {
    if (!createUserFormElement) {
      throw new Error("Input Element not found");
    }

    createUserFormElement.setAttribute("data-sign-up", "true");

    if (EVENT_TARGET_VALUE === "") {
      createUserFormElement.removeAttribute("data-sign-up");
    }
  } else if (EVENT_TARGET.dataset.js === "loginUsername") {
    if (!loginFormElement) {
      throw new Error("Input Element not found");
    }

    loginFormElement.setAttribute("data-login", "true");

    if (EVENT_TARGET_VALUE === "") {
      loginFormElement.removeAttribute("data-login");
    }
  }
});

//This function handles function submiting for fata

document.addEventListener("submit", (event) => {
  const EVENT_TARGET = event.target;
  if (EVENT_TARGET.dataset.js === "createUserForm") {
    if (!createUserFormElement) {
      throw new Error("Input Element not found");
    } else {
      event.preventDefault();
      submitFormData(event);
    }
  } else if (EVENT_TARGET.dataset.js === "loginForm") {
    if (!loginFormElement) {
      throw new Error("Input Element not found");
    } else {
      event.preventDefault();
      submitFormData(event);
    }
  }
});

//This function handles document click events through event delegation
document.addEventListener("click", (event) => {
  const EVENT_TARGET = event.target;
  if (EVENT_TARGET.dataset.js === "logOutUserButton") {
    const LOG_OUT_URL = "/connect.php";
    const action_type = "user_logout";
    postData(LOG_OUT_URL, action_type, {});
  }
});

////This function handles the submission of form data

function submitFormData(event) {
  const POST_DATA_URL = "/connect.php";

  const FORM_DATA = new FormData(event.target);
  const FORM_DATA_OBJECT = Object.fromEntries(FORM_DATA);
  const isSignUp = createUserFormElement.getAttribute("data-sign-up");
  const isLogin = loginFormElement.getAttribute("data-login") === "true";
  if (isSignUp) {
    postData(POST_DATA_URL, "user_sign_up", FORM_DATA_OBJECT);
  } else if (isLogin) {
    postData(POST_DATA_URL, "user_login", FORM_DATA_OBJECT);
  }
}
