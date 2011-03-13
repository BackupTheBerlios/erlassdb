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
    var encodedName = encodeURIComponent(checkbox.name);
    var query = 'filter=' + encodedName;
    var list = document.getElementsByName(checkbox.name);
    for (var i = 0; i < list.length; i++) {
        var item = list.item(i);
        if (item.checked) {
            query += '&' + encodedName + '=' + encodeURIComponent(item.value);
        }
    }
    $mode = checkbox.checked ? 'remove' : 'add';
    query += '&mode=' + $mode;
    var xmlHttp = createXMLHttpRequest();
    xmlHttp.open('POST', './', true);
    xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHttp.setRequestHeader("Content-length", query.length);
    xmlHttp.setRequestHeader("Connection", "close");
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4) {
            //alert(xmlHttp.responseText);
            var boxes = xmlHttp.responseText.split("\n");
            for (var i = 0; i < boxes.length; i++) {
                var boxId = boxes[i];
                if (boxId.length < 1) {
                    continue;
                }
                var box = document.getElementById(boxId);
                box.disabled = checkbox.checked ? true : false;
            }
        }
    };
    xmlHttp.send(query);
}

