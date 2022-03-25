window.readyHandlers = [];
function ready(handler) {
    window.readyHandlers.push(handler);
    handleState();
};

window.ready = ready;

window.handleState = function handleState () {
    if (document.readyState === 'interactive' || document.readyState === "complete") {
        while(window.readyHandlers.length > 0) {
            (window.readyHandlers.shift())();
        }
    }
};

document.onreadystatechange = window.handleState;

export { ready };
