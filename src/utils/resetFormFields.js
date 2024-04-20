//This function resets the form fields to their default values and turns autocomplete off.
//Parameters: form (HTMLFormElement) - the form element to be reset
//Returns: none

export default function resetFormFields(form) {
  if (!form) {
    throw new Error("No form element found");
  }
  form.reset();
  form.setAttribute("autocomplete", "off");
}
