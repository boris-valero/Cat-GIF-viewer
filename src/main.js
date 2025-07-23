import { generateur, generateUrl, imagePath } from "@nextcloud/router";
import { loadState } from "@nextcloud/initial-state";
import axios from "@nextcloud/axios";
import { showSuccess, showError } from "@nextcloud/dialogs";

function main() {
  const state = loadState("catgifs", "tutorial_initial_state");
  const tutorialDiv = document.querySelector("#app-content #catgifs");
  addConfigButton(tutorialDiv, state);
  addGifs(tutorialDiv, state);
}

function addGifs(container, state) {
  const fileNameList = state.file_name_list;
  fileNameList.forEach((name) => {
    const fileDiv = document.createElement("div");
    fileDiv.classList.add("gif-wrapper");
    const img = document.createElement("img");
    img.setAttribute("src", imagePath("catgifs", "gifs/" + name));
    img.style.display = "none";
    const button = document.createElement("button");
    button.innerText = "Show/Hide " + name;
    button.addEventListener("click", (e) => {
      if (img.style.display === "block") {
        img.style.display = "none";
      } else {
        img.style.display = "block";
      }
    });
    fileDiv.append(button);
    fileDiv.append(img);
    container.append(fileDiv);
  });
}

function addConfigButton(container, state) {
  const themeButton = document.createElement("button");
  themeButton.innerText =
    state.fixed_gif_size === "1"
      ? "Enable variable gif size"
      : "Enable fixed gif size";
  if (state.fixed_gif_size === "1") {
    container.classList.add("fixed-size");
  }
  themeButton.addEventListener("click", (e) => {
    if (state.fixed_gif_size === "1") {
      state.fixed_gif_size = "0";
      themeButton.innerText = "Enable fixed gif size";
      container.classList.remove("fixed-size");
    } else {
      state.fixed_gif_size = "1";
      themeButton.innerText = "Enable variable gif size";
      container.classList.add("fixed-size");
    }
    const url = generateUrl("/apps/catgifs/config");
    const params = {
      key: "fixed gif size",
      value: state.fixed_gif_size,
    };
    axios
      .put(url, params)
      .then((response) => {
        showSuccess("Settings saved: " + response.data.message);
      })
      .catch((error) => {
        showError("Failed to save settings: " + error.response.data.message);
        console.error(error);
      });
  });
  container.append(themeButton);
}
document.addEventListener("DOMContentLoaded", (event) => {
  main();
});
