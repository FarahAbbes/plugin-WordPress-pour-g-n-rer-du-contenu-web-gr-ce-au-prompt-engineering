let currentFieldType = "";

function showFieldModal(type) {
  currentFieldType = type;
  const modalTitle = document.querySelector("#textFieldModal .modal-title");
  const customizationContent = document.getElementById("customization-content");

  modalTitle.textContent = `Générer une balise de champ ${type}`;

  const commonFields = getCommonFields(type);
  const specificFields = getSpecificFields(type);

  customizationContent.innerHTML = commonFields + specificFields;
  $("#textFieldModal").modal("show");
}

function getCommonFields(type) {
  let fields = `
    <div class="form-group">
      <label for="isRequired">
        <input type="checkbox" id="isRequired"> Champ obligatoire
      </label>
    </div>
    <div class="form-group">
      <label for="fieldName">Nom</label>
      <input type="text" class="form-control" id="fieldName" placeholder="Entrez le nom du champ">
    </div>
  `;

  if (
    ["text", "email", "url", "number", "tel", "date", "textarea"].includes(type)
  ) {
    fields += `
      <div class="form-group">
        <label for="defaultValue">Valeur par défaut</label>
        <input type="text" class="form-control" id="defaultValue" placeholder="Entrez la valeur par défaut">
      </div>
      <div class="form-group">
        <label>
          <input type="checkbox" id="usePlaceholder"> Utilisez ce texte comme texte indicatif du champ.
        </label>
      </div>
    `;
  }

  if (type !== "submit") {
    fields += `
      <div class="form-group">
        <label for="fieldId">Attribut «id»</label>
        <input type="text" class="form-control" id="fieldId" placeholder="Entrez l'id du champ">
      </div>
      <div class="form-group">
        <label for="fieldClass">Attribut «class»</label>
        <input type="text" class="form-control" id="fieldClass" placeholder="Entrez la classe du champ">
      </div>
    `;
  }
  if (type === "submit") return ""; // Pas de champs communs pour 'submit'

  return fields;
}

function getSpecificFields(type) {
  if (type === "number") {
    return `
      <div class="form-group">
        <label for="minValue">Valeur minimale</label>
        <input type="number" class="form-control" id="minValue" placeholder="Entrez la valeur minimale">
      </div>
      <div class="form-group">
        <label for="maxValue">Valeur maximale</label>
        <input type="number" class="form-control" id="maxValue" placeholder="Entrez la valeur maximale">
      </div>
      <div class="form-group">
        <label for="numberFieldType">Type de champ</label>
        <select class="form-control" id="numberFieldType">
          <option value="counter">Compteur</option>
          <option value="slider">Curseur</option>
        </select>
      </div>
    `;
  }
  if (type === "date") {
    return `
      <div class="form-group">
        <label for="minDate">Date minimale</label>
        <input type="date" class="form-control" id="minDate">
      </div>
      <div class="form-group">
        <label for="maxDate">Date maximale</label>
        <input type="date" class="form-control" id="maxDate">
      </div>
    `;
  }
  if (type === "select") {
    return `
      <div class="form-group">
        <label for="selectOptions">Options</label>
        <textarea class="form-control" id="selectOptions" rows="3" placeholder="Une option par ligne."></textarea>
      </div>
      <div class="form-group">
        <label>
          <input type="checkbox" id="allowMultipleSelections"> Permettre les sélections multiples
        </label>
      </div>
      <div class="form-group">
        <label>
          <input type="checkbox" id="insertBlankOption"> Insérer un choix vide comme première option.
        </label>
      </div>
    `;
  }
  if (type === "checkbox") {
    return `
      <div class="form-group">
        <label>Options</label>
        <textarea class="form-control" id="checkboxOptions" rows="3" placeholder="Une option par ligne."></textarea>
      </div>
      <div class="form-group">
        <label>
          <input type="checkbox" id="labelFirst"> Mettre un libellé puis la case à cocher.
        </label>
      </div>
      <div class="form-group">
        <label>
          <input type="checkbox" id="wrapLabel"> Entourer chaque élément avec un libellé.
        </label>
      </div>
      <div class="form-group">
        <label>
          <input type="checkbox" id="exclusiveCheckboxes"> Rendre les cases à cocher exclusives.
        </label>
      </div>
    `;
  }
  if (type === "radio") {
    return `
      <div class="form-group">
        <label>Options</label>
        <textarea class="form-control" id="radioOptions" rows="3" placeholder="Une option par ligne."></textarea>
      </div>
      <div class="form-group">
        <label>
          <input type="checkbox" id="labelFirst"> Mettre un libellé puis la case à cocher.
        </label>
      </div>
      <div class="form-group">
        <label>
          <input type="checkbox" id="wrapLabel"> Entourer chaque élément avec un libellé.
        </label>
      </div>
    `;
  }

  if (type === "file") {
    return `
      <div class="form-group">
        <label for="maxFileSize">Limite de taille maximale (en octets)</label>
        <input type="number" class="form-control" id="maxFileSize">
      </div>
      <div class="form-group">
        <label for="acceptedFileFormats">Formats de fichiers acceptés (séparés par des virgules)</label>
        <input type="text" class="form-control" id="acceptedFileFormats" placeholder="png,jpg,pdf">
      </div>
    `;
  }
  if (type === "submit") {
    return `
      <div class="form-group">
        <label for="submitLabel">Libellé</label>
        <input type="text" class="form-control" id="submitLabel" placeholder="Entrez le libellé du bouton">
      </div>
      <div class="form-group">
        <label for="submitId">Attribut «id»</label>
        <input type="text" class="form-control" id="submitId" placeholder="Entrez l'id du champ">
      </div>
      <div class="form-group">
        <label for="submitClass">Attribut «class»</label>
        <input type="text" class="form-control" id="submitClass" placeholder="Entrez la classe du champ">
      </div>
    `;
  }
  if (type === "confirmation") {
    return `
      
      <div class="form-group">
        <label for="confirmationCondition">Condition</label>
        <textarea class="form-control" id="confirmationCondition" rows="2"></textarea>
      </div>
      <div class="form-group">
        <label>
          <input type="checkbox" id="optionalConfirmation"> Rendre cette case à cocher facultative
        </label>
      </div>
    `;
  }
  return "";
}

function generateFieldTag() {
  const fieldName = document.getElementById("fieldName").value;
  const fieldId = document.getElementById("fieldId")?.value;
  const fieldClass = document.getElementById("fieldClass")?.value;

  let tag = `[${currentFieldType}`;

  if (
    [
      "text",
      "email",
      "url",
      "tel",
      "textarea",
      "select",
      "checkbox",
      "date",
      "number",
      "file",
    ].includes(currentFieldType)
  ) {
    const isRequired = document.getElementById("isRequired").checked;
    if (isRequired) tag += "*";
  }

  if (fieldName && currentFieldType !== "submit") tag += ` ${fieldName}`;
  if (currentFieldType === "submit") {
    const submitLabel = document.getElementById("submitLabel")?.value;
    const submitId = document.getElementById("submitId")?.value;
    const submitClass = document.getElementById("submitClass")?.value;

    if (submitLabel) tag += ` "${submitLabel}"`;
    if (submitId) tag += ` id:${submitId}`;
    if (submitClass) tag += ` class:${submitClass}`;
  } else {
    if (fieldId) tag += ` id:${fieldId}`;
    if (fieldClass) tag += ` class:${fieldClass}`;
  }

  if (["text", "email", "url", "tel", "textarea"].includes(currentFieldType)) {
    const defaultValue = document.getElementById("defaultValue").value;
    const usePlaceholder = document.getElementById("usePlaceholder").checked;
    if (defaultValue) {
      if (usePlaceholder) {
        tag += ` placeholder "${defaultValue}"`;
      } else {
        tag += ` "${defaultValue}"`;
      }
    }
  } else if (currentFieldType === "number") {
    const numberFieldType = document.getElementById("numberFieldType").value;
    tag = `[${numberFieldType === "counter" ? "number" : "range"}`;
    if (document.getElementById("isRequired").checked) tag += "*";
    if (fieldName) tag += ` ${fieldName}`;
  } else if (currentFieldType === "date") {
    const minDate = document.getElementById("minDate").value;
    const maxDate = document.getElementById("maxDate").value;
    if (minDate) tag += ` min:${minDate}`;
    if (maxDate) tag += ` max:${maxDate}`;
  } else if (currentFieldType === "select" || currentFieldType === "checkbox") {
    if (currentFieldType === "select") {
      const allowMultipleSelections = document.getElementById(
        "allowMultipleSelections"
      ).checked;
      const insertBlankOption =
        document.getElementById("insertBlankOption").checked;
      if (allowMultipleSelections) tag += " multiple";
      if (insertBlankOption) tag += " include_blank";
    } else if (currentFieldType === "checkbox") {
      const labelFirst = document.getElementById("labelFirst").checked;
      const wrapLabel = document.getElementById("wrapLabel").checked;
      const exclusiveCheckboxes = document.getElementById(
        "exclusiveCheckboxes"
      ).checked;
      if (labelFirst) tag += " label_first";
      if (wrapLabel) tag += " use_label_element";
      if (exclusiveCheckboxes) tag += " exclusive";
    }
    const options = document
      .getElementById(`${currentFieldType}Options`)
      .value.split("\n");
    options.forEach((option) => {
      if (option.trim() !== "") {
        tag += ` "${option.trim()}"`;
      }
    });
  } else if (currentFieldType === "radio") {
    const labelFirst = document.getElementById("labelFirst").checked;
    const wrapLabel = document.getElementById("wrapLabel").checked;
    if (labelFirst) tag += " label_first";
    if (wrapLabel) tag += " use_label_element";
    tag += " default:1";
    const options = document.getElementById("radioOptions").value.split("\n");
    options.forEach((option) => {
      if (option.trim() !== "") {
        tag += ` "${option.trim()}"`;
      }
    });
  } else if (currentFieldType === "file") {
    tag = `[file`;
    if (isRequired) tag += "*";

    if (fieldName) tag += ` ${fieldName}`;
    const maxFileSize = document.getElementById("maxFileSize").value;
    const acceptedFileFormats = document.getElementById(
      "acceptedFileFormats"
    ).value;
    if (maxFileSize) tag += ` limit:${maxFileSize}`;
    if (acceptedFileFormats) tag += ` filetypes:${acceptedFileFormats}`;
    if (fieldId) tag += ` id:${fieldId}`;
    if (fieldClass) tag += ` class:${fieldClass}`;
    //tag += `]`;
  } else if (currentFieldType === "confirmation") {
    const condition = document.getElementById("confirmationCondition").value;
    const optional = document.getElementById("optionalConfirmation").checked;
    tag = `[acceptance ${fieldName} id:${fieldId} class:${fieldClass}`;
    if (optional) tag += " optional";
    tag += `] ${condition} [/acceptance`;
  }

  if (currentFieldType === "submit") {
    const submitLabel = document.getElementById("submitLabel").value;
    tag = `[submit id:${fieldId} class:${fieldClass} "${submitLabel}"]`;
  }

  tag += "]";

  const formCode = document.getElementById("formCode");
  formCode.value += tag + "\n";

  $("#textFieldModal").modal("hide");
}
function addCustomField() {
  const form = document.getElementById("dynamic-form");
  const fieldName = document.getElementById("fieldName").value;
  const isRequired = document.getElementById("isRequired").checked;
  const defaultValue = document.getElementById("defaultValue")?.value;
  const fieldId = document.getElementById("fieldId")?.value;
  const fieldClass = document.getElementById("fieldClass")?.value;

  let additionalAttributes = "";
  if (currentFieldType === "number") {
    const minValue = document.getElementById("minValue").value;
    const maxValue = document.getElementById("maxValue").value;
    if (minValue !== "") additionalAttributes += ` min="${minValue}"`;
    if (maxValue !== "") additionalAttributes += ` max="${maxValue}"`;
  }

  let newField = `
    <div class="form-group">
      <label for="${fieldId}">${fieldName}</label>
      <input type="${currentFieldType}" 
             class="form-control ${fieldClass}" 
             id="${fieldId}" 
             name="${fieldName}"
             value="${defaultValue || ""}"
             ${isRequired ? "required" : ""}
             ${additionalAttributes}>
    </div>
  `;

  form.innerHTML += newField;
  $("#textFieldModal").modal("hide");
}

function parseFieldTag(tag) {
  const regex =
    /\[(\w+)(\*)?\s*(\w+)?\s*"?([^"]*)"?\s*(id:\w+)?\s*(class:[\w-]+)?\s*(min:\d+)?\s*(max:\d+)?\]/;
  const match = tag.match(regex);
  return {
    type: match[1],
    isRequired: match[2] === "*",
    name: match[3] || "",
    defaultValue: match[4] || "",
    id: match[5] ? match[5].split(":")[1] : "",
    class: match[6] ? match[6].split(":")[1] : "",
    min: match[7] ? match[7].split(":")[1] : "",
    max: match[8] ? match[8].split(":")[1] : "",
  };
}

function createFieldHTML(fieldInfo) {
  if (fieldInfo.type === "select") {
    let options = "";
    if (fieldInfo.insertBlankOption) {
      options += '<option value=""></option>\n';
    }
    fieldInfo.options.forEach((option) => {
      options += `<option value="${option}">${option}</option>\n`;
    });

    return `
      <div class="form-group">
        <label for="${fieldInfo.id}">${fieldInfo.name}</label>
        <select 
          class="form-control ${fieldInfo.class}" 
          id="${fieldInfo.id}" 
          name="${fieldInfo.name}"
          ${fieldInfo.isRequired ? "required" : ""}
          ${fieldInfo.allowMultipleSelections ? "multiple" : ""}>
          ${options}
        </select>
      </div>
    `;
  }
  if (fieldInfo.type === "number") {
    const inputType =
      fieldInfo.numberFieldType === "slider" ? "range" : "number";
    return `
      <div class="form-group">
        <label for="${fieldInfo.id}">${fieldInfo.name}</label>
        <input type="${inputType}" 
               class="form-control ${fieldInfo.class}" 
               id="${fieldInfo.id}" 
               name="${fieldInfo.name}"
               value="${fieldInfo.defaultValue}"
               ${fieldInfo.isRequired ? "required" : ""}
               ${fieldInfo.min ? `min="${fieldInfo.min}"` : ""}
               ${fieldInfo.max ? `max="${fieldInfo.max}"` : ""}
        >
        ${
          fieldInfo.numberFieldType === "counter"
            ? `<button type="button" onclick="decrementCounter('${fieldInfo.id}')">-</button>
           <button type="button" onclick="incrementCounter('${fieldInfo.id}')">+</button>`
            : ""
        }
      </div>
    `;
  }
}

function incrementCounter(id) {
  const input = document.getElementById(id);
  const step = input.step ? parseFloat(input.step) : 1;
  const max = input.max ? parseFloat(input.max) : Infinity;
  input.value = Math.min(parseFloat(input.value) + step, max);
}
function saveHTML() {
  const htmlCode = document.getElementById("formCode").value;
  const fileName = document.getElementById("fileName").value || "default";

  $.ajax({
    url: "generate_content.php",
    method: "POST",
    data: {
      formCode: htmlCode,
      fileName: fileName,
    },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        // Afficher le contenu généré dans une nouvelle fenêtre ou un modal
        displayGeneratedContent(response.content);
      } else {
        alert("Erreur lors de la génération du contenu : " + response.message);
      }
    },
    error: function (xhr, status, error) {
      console.error("Erreur de requête AJAX : ", status, error);
      alert("Erreur lors de la génération du contenu.");
    },
  });
}

function displayGeneratedContent(content) {
  // Ouvrir une nouvelle fenêtre avec le contenu généré
  var win = window.open("", "_blank");
  win.document.write(content);
  win.document.close();
}
function addNewTemplateToList(templateName) {
  const dynamicTemplates = document.getElementById("dynamic-templates");
  if (dynamicTemplates) {
    const newButton = document.createElement("button");
    newButton.className = "template-button";
    newButton.textContent = templateName;
    newButton.onclick = function () {
      window.location.href = `edit_template.php?name=${encodeURIComponent(
        templateName
      )}`;
    };
    dynamicTemplates.appendChild(newButton);
  }
}
// Chargez les templates existants au chargement de la page
$(document).ready(function () {
  loadExistingTemplates();
});

function loadExistingTemplates() {
  fetch("get_templates.php")
    .then((response) => response.json())
    .then((templates) => {
      const dynamicTemplates = document.getElementById("dynamic-templates");
      templates.forEach((template) => {
        const button = document.createElement("button");
        button.className = "template-button";
        button.textContent = template.nom_fichier;
        button.onclick = function () {
          window.location.href = `edit_template.php?name=${encodeURIComponent(
            template.nom_fichier
          )}`;
        };
        dynamicTemplates.appendChild(button);
      });
    })
    .catch((error) =>
      console.error("Erreur lors du chargement des templates:", error)
    );
}
function submitForm() {
  const formData = new FormData(document.getElementById("dynamic-form"));
  formData.append("template_id", currentTemplateId); // Assurez-vous d'avoir une variable currentTemplateId définie

  fetch("submit_form.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert(data.message);
        // Réinitialiser le formulaire ou rediriger l'utilisateur
      } else {
        alert("Erreur : " + data.message);
      }
    })
    .catch((error) => {
      console.error("Erreur:", error);
      alert("Une erreur est survenue lors de la soumission du formulaire.");
    });
}
document.getElementById("submit-button").addEventListener("click", submitForm);
