
//Upload Scripts
function setCheck(target) {
    target.nextSibling.value = target.checked ? 1:0;
    console.log("lol", target.checked, target.nextSibling.value);
}
function addNew() {
    console.log("Adding new row");
    var tbody = document.getElementById("products");
    //'<tr><td><input type="text" placeholder="Produkt" name="produkter_namn[]"/></td><td><input type="text" placeholder="Pris" name="produkter_pris[]"/></td><td><input type="checkbox" name="produkter_exclude[]"/></td></tr>';
    var tr = document.createElement("TR");
    console.log(tr);
    var td, input;

    td = document.createElement("TD");
    input = document.createElement("input");
    input.setAttribute("type", "hidden");
    input.setAttribute("name", "produkter_id[]");
    td.appendChild(input);

    input = document.createElement("input");
    var focusinput = input;
    input.setAttribute("placeholder", "Produkt");
    input.setAttribute("name", "produkter_namn[]");
    td.appendChild(input);
    tr.appendChild(td);

    td = document.createElement("TD");
    input = document.createElement("input");
    input.setAttribute("placeholder", "Pris");
    input.setAttribute("name", "produkter_pris[]");
    td.appendChild(input);
    tr.appendChild(td);

    td = document.createElement("TD");
    input = document.createElement("input");
    input.setAttribute("type", "checkbox");
    //input.setAttribute("name", "produkter_exclude[]");
    input.setAttribute("onclick", 'setCheck(this)');
    td.appendChild(input);
    input = document.createElement("input");
    input.setAttribute("type", "hidden");
    input.setAttribute("name", "produkter_exclude[]");
    input.setAttribute("value", "0");
    td.appendChild(input);
    tr.appendChild(td);
    //td = document.createElement("td").innerHTML = '<input type="text" placeholder="Pris" name="produkter_pris[]"/>';
    //tr.appendChild(td);
    //td = document.createELement("td").innerHTML = '<input type="checkbox" name="produkter_exclude[]"/>';
    //tr.appendChild(td);

    tbody.appendChild(tr);
    focusinput.focus();
}
function checkEnter(e) {
	if (e.keyCode == 13 ) {
		// If Enter (13), add new field
		addNew();
		if(e.preventDefault) {
            e.preventDefault();
        }
	}
}