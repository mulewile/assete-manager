//This function creates a table body based on the data passed to it
//Parameters: data (array of objects) - the data to be displayed in the table. - the object name. - the table where the table body will be appended.
//Returns: the table body element

export function create_table_body(
  table_data,
  table_body_element,
  table_element
) {
  if (!Array.isArray(table_data)) {
    throw new Error("The data must be an array");
  }
  if (!table_body_element) {
    throw new Error("No table body element found");
  }
  if (!table_element) {
    throw new Error("No table element found");
  }

  table_data.forEach((data) => {
    const table_row = document.createElement("tr");

    const table_th = table_element.querySelectorAll("tr th");
    table_th.forEach((th_element) => {
      const detail = th_element.dataset.name;
      const dataCell = document.createElement("td");

      table_row.setAttribute("data-name", detail);

      dataCell.textContent = data[detail];

      table_row.appendChild(dataCell);
    });

    table_body_element.appendChild(table_row);
  });
}
