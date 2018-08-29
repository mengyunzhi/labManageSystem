
    //onchang的方法完成级联表单操作
function getMajor() {
    var major1 = document.getElementById('major1').innerHTML;
    var collegeNode = document.getElementById('college');
    var majorNode = document.getElementById('major');
    var name = "aCollege";//建立的input的name
    clear(majorNode);
    var collegeIndex = collegeNode.selectedIndex;
    var value = collegeNode[collegeIndex].value;
    var url = "/labmanagesystem/public/index/login/getMajor?college=" + value;
    ajaxGet(url, function (response) {
        var majors = response;
        createOption(majorNode, majors);
        setSelected(majorNode, major1);
        console.log(majors);
    });
    creatInput(value, name, collegeNode);
}

function getGrade() {
    var grade1 = document.getElementById('grade1').innerHTML;
    var majorNode = document.getElementById('major');
    var gradeNode = document.getElementById('grade');
    var name = "aMajor";//建立的input的name

    clear(gradeNode);

    var majorIndex = majorNode.selectedIndex;
    var value = majorNode[majorIndex].value;
    var url = "/labmanagesystem/public/index/login/getGrade?major=" + value;
    ajaxGet(url, function (response) {
        var grades = response;
        createOption(gradeNode, grades);
        setSelected(gradeNode, grade1);
    });
    creatInput(value, name, majorNode);
}

function getKlass() {
    var klass1 = document.getElementById('klass1').innerHTML;
    var gradeNode = document.getElementById('grade');
    var klassNode = document.getElementById('aklass');
    var name = "aGrade";//建立的input的name

    clear(klassNode);

    var gradeIndex = gradeNode.selectedIndex;
    var value = gradeNode[gradeIndex].value;
    var url = "/labmanagesystem/public/index/login/getKlass?grade=" + value;
    ajaxGet(url, function (response) {
        var klasses = response;
        createOption(klassNode, klasses);
        setSelected(klassNode, klass1);
    });
    creatInput(value, name, gradeNode);
}

function getKlassId() {
    var klassNode = document.getElementById('aklass');
    var klassIndex = klassNode.selectedIndex;
    var klassId = klassNode[klassIndex].value;
    var name = "klassId"//建立的input的name
    creatInput(klassId, name, klassNode);
}

//访问php函数，一个对象数组的返回值
function ajaxGet(url, callback) {
    $.ajax({
        url: url,
        type: "get",
        success: function (response) {
            callback(response);
        },
        error: function (xhr) {
            console.log('server error');
        }
    });
}

//清除
function clear(node) {
    node.length = 0;
}

//创建选项
function createOption(node, inners, values) {
    for (var i = 0; i < inners.length; i++) {
        var option = document.createElement('option');

        option.value = inners[i].id;
        option.name = node;
        option.innerHTML = inners[i].name;
        node.appendChild(option);
    }
}

//设置选中状态
function setSelected(node, value) {
    for (var i = 0; i < node.options.length; i++) {
        if (node.options[i].innerHTML == value) {
            node.options[i].setAttribute("selected", "true");
        }
    }
}

//创建一个input按钮
function creatInput(value, name, position) {
    var newInput = document.createElement("input");
    newInput.name = name;
    newInput.value = value;
    position.appendChild(newInput);
}