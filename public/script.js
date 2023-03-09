function event_collapse(arrow, content) {
    if (arrow.classList.contains("up")) {
        content.classList.remove("show");
        content.classList.add("hide");
        arrow.classList.add("down");
        arrow.classList.remove("up");
    } else {
        content.classList.remove("hide");
        content.classList.add("show");
        arrow.classList.add("up");
        arrow.classList.remove("down");
    }
}

var toggles = document.querySelectorAll('.collapse-header');

toggles.forEach(function(toggle) {
    toggle.addEventListener('click', function(event) {
        let arrow = event.target.querySelectorAll(".arrow");
        arrow = arrow[0];
        let content = event.target.parentNode.querySelectorAll(".collapse-content");
        content = content[0];

        if (arrow !== undefined && content !== undefined) {
            event_collapse(arrow, content);
        }
    });

    Array.from(toggle.children).forEach(function(child) {
        child.addEventListener('click', function(event) {
            let arrow = event.target.parentNode.querySelectorAll(".arrow");
            arrow = arrow[0];
            let content = event.target.parentNode.parentNode.querySelectorAll(".collapse-content");
            content = content[0];

            if (arrow !== undefined && content !== undefined) {
                event_collapse(arrow, content);
            }
        });

        Array.from(child.children).forEach(function(child2) {
        child2.addEventListener('click', function(event) {
            let arrow = event.target.parentNode.parentNode.querySelectorAll(".arrow");
            arrow = arrow[0];
            let content = event.target.parentNode.parentNode.parentNode.querySelectorAll(".collapse-content");
            content = content[0];

            if (arrow !== undefined && content !== undefined) {
                event_collapse(arrow, content);
            }
        });
    });
    });
});