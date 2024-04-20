//The function gets a single dom element by its data-js attribute. We leave ids and classes for CSS and use data-js for JavaScript. This way, we can change the id or class without breaking the JavaScript.
//Parameters: selector (string) - the data-js attribute of the element to be selected
//Returns: the selected element

export default function getDOMElement(selector) {
  const element = document.querySelector(`[data-js="${selector}"]`);

  if (!element) {
    throw new Error(`No element with the data-js of ${selector} found`);
  }
  return element;
}
