import {
  dom,
  text,
  getTree,
  getSecretURL,
  updateSecretURL,
  updateTree,
  createExpandCollapseCallback,
  getExpanded,
  insertTeam,
  deleteTeam,
} from "./framework.js";

export function ui(tree, url, expanded, element) {
  url = getSecretURL();
  element.parentNode.replaceChild(
    dom(
      "div",
      { id: "ui" },
      expandCollapseButton(),
      domTree(tree),
      domSecretURL(url),
      dom(
        "button",
        { style: "margin-top: 24px;", click: save },
        text("✅ Save")
      )
    ),
    element
  );
}

function save() {
  const form = dom(
    "form",
    { method: "POST", action: "class-rusty-inc-org-chart-plugin.php" },
    dom("input", {
      name: "tree",
      type: "hidden",
      value: JSON.stringify(getTree()),
    }),
    dom("input", {
      name: "key",
      type: "hidden",
      value: JSON.stringify(getSecretURL()),
    })
  );
  document.body.appendChild(form);
  form.submit();
}

function askUserForTeamDetails() {
  const emoji = window.prompt("Enter new team’s emoji:");
  if (null === emoji) {
    return;
  }
  const name = window.prompt("Enter new team’s name:");
  if (null === name) {
    return;
  }
  return { name, emoji };
}

function askUserForDeleteConfirmation() {
  return window.confirm(
    "Are you sure you want to delete the team and all of its subteams?"
  );
}

function expandCollapseButton() {
  const expanded = getExpanded();
  //reduced form 1500 to 5 - for speed up thr rendering
  const expandCollapse = createExpandCollapseCallback(
    "#ui > .team",
    ".children",
    5
  );
  return dom(
    "button",
    { style: "margin-bottom: 24px;", click: expandCollapse },
    text((expanded ? "Collapse" : "Expand") + " tree")
  );
}

function domTree(team, level = 0) {
  const expanded = getExpanded();
  return dom(
    "div",
    {
      class: "team",
      style: `padding-left: ${
        level * 20
      }px; overflow: hidden; position: relative;`,
    },
    dom(
      "div",
      {
        class: "entry",
        style: "z-index: 2; position: relative; background: #f1f1f1;",
      },
      dom("span", { style: "font-size: 3em;" }, text(team.emoji)),
      text(` ${team.name} `),
      dom(
        "button",
        {
          click: () => addTeam(askUserForTeamDetails(), team.id),
          title: "Add subteam",
        },
        text("➕")
      ),
      dom(
        "button",
        {
          click: () =>
            askUserForDeleteConfirmation() ? removeTeam(team.id) : null,
          title: "Delete subteam",
        },
        text("🚫")
      )
    ),
    dom(
      "div",
      {
        class: "children",
        style:
          "z-index: 1; position: relative; display: " +
          (expanded ? "block" : "none"),
      },
      ...Object.keys(team.children).map((id) =>
        domTree(team.children[id], level + 1)
      )
    )
  );
}

function domSecretURL(url) {
  url = getSecretURL();
  return dom(
    "p",
    {},
    text("Secret URL to share: "),
    dom(
      "strong",
      {},
      text(url ? url : "will be regenerated on save")
    ),
    text(" "),
    url
      ? dom(
          "button",
          {
            click: () => updateSecretURL(null),
            title: "Regenerate",
          },
          text("🔁")
        )
      : null,
  );
}

function addTeam(userInput, parentTeamId) {
  const { name, emoji } = userInput;

  const newTeam = {
    name,
    emoji,
    parent_id: parentTeamId,
    children: [],
  };

  const newTree = insertTeam(newTeam);
  updateTree(newTree);
}

function removeTeam(teamIdToRemove) {
  const newTree = deleteTeam(teamIdToRemove);
  updateTree(newTree);
}
