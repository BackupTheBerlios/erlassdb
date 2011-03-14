function createXMLHttpRequest() {
    try {
        // Mozilla, Opera, Safari sowie Internet Explorer (ab v7)
        return new XMLHttpRequest();
    } catch(e) {
        try {
            // MS Internet Explorer (ab v6)
            return new ActiveXObject("Microsoft.XMLHTTP");
        } catch(e) {
            // MS Internet Explorer (ab v5)
            return new ActiveXObject("Msxml2.XMLHTTP");
        }
    }
}

function filterCheckboxes(checkbox) {
    var name = checkbox.name.substr(0, checkbox.name.length - 2);
    var query = 'filter=' + encodeURIComponent(name);
    var list = document.getElementsByName(checkbox.name);
    for (var i = 0; i < list.length; i++) {
        var item = list.item(i);
        if (item.checked) {
            query += '&' + encodeURIComponent(checkbox.name)
            + '=' + encodeURIComponent(item.value);
        }
    }
    var http = createXMLHttpRequest();
    http.open('POST', './', true);
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    http.setRequestHeader("Content-length", query.length);
    http.setRequestHeader("Connection", "close");
    http.onreadystatechange = function () {
        if (http.readyState == 4) {
            var availBoxes = http.responseText.split("\n");
            var boxMap = new Object();
            for (var i = 0; i < availBoxes.length; i++) {
                var boxId = availBoxes[i];
                if (boxId.length < 1) {
                    continue;
                }
                var box = document.getElementById(boxId);
                if (typeof boxMap[box.name] == 'undefined') {
                    boxMap[box.name] = new Object();
                }
                boxMap[box.name][boxId] = true;
            }

            for (var boxname in boxMap) {
                var list = document.getElementsByName(boxname);
                for (i = 0; i < list.length; i++) {
                    var item = list.item(i);
                    var disabled;
                    if (typeof boxMap[boxname][item.id] != 'undefined'
                        &&  boxMap[boxname][item.id] == true) {
                        disabled = false;
                    } else {
                        disabled = true;
                    }
                    item.disabled = disabled;
                }
            }
        }
    };
    http.send(query);
}

