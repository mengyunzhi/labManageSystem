'use strict';

window.yunzhi = {};

//onchang的方法完成级联表单操作
yunzhi.getMajor = function (collegeName, majorName, gradeName, klassName, searchCollege, searchMajor, searchGrade) {
    var collegeNode = document.getElementById(collegeName);
    var majorNode = document.getElementById(majorName);
    var name = "aCollege";//建立的input的name
    clear(majorNode);
    var collegeIndex = collegeNode.selectedIndex;
    var value = collegeNode[collegeIndex].value;
    var url = "/labmanagesystem/public/index/login/getMajor?college=" + value;
    ajaxGet(url, function (response) {
        createOption(majorNode, response);
        setSelected(majorNode, searchMajor);
        yunzhi.getGrade(majorName, gradeName, klassName, searchMajor, searchGrade);
    });
    createInput(value, name, collegeNode);
};

yunzhi.getGrade = function (majorName, gradeName, klassName, searchMajor, searchGrade) {
    var majorNode = document.getElementById(majorName);
    var gradeNode = document.getElementById(gradeName);
    var name = "aMajor";//建立的input的name

    clear(gradeNode);

    var majorIndex = majorNode.selectedIndex;
    var value = majorNode[majorIndex].value;

    var url = "/labmanagesystem/public/index/login/getGrade?major=" + value;
    ajaxGet(url, function (response) {
        createOption(gradeNode, response);

        setSelected(gradeNode,searchGrade);
        yunzhi.getKlass(gradeName, klassName, searchGrade);

    });
    createInput(value, name, majorNode);
};

yunzhi.getKlass = function (gradeName, klassName, searchGrade) {
    var gradeNode = document.getElementById(gradeName);
    var klassNode = document.getElementById(klassName);
    var name = "aGrade";//建立的input的name

    clear(klassNode);

    var gradeIndex = gradeNode.selectedIndex;
    var value = gradeNode[gradeIndex].value;
    var url = "/labmanagesystem/public/index/login/getKlass?grade=" + value;
    ajaxGet(url, function (response) {
        createOption(klassNode, response);
        setSelected(klassNode, searchGrade);
        yunzhi.getKlassId(klassName);
    });
    createInput(value, name, gradeNode);
};

yunzhi.getKlassId = function (klassName) {
    var klassNode = document.getElementById(klassName);
    var klassIndex = klassNode.selectedIndex;
    var klassId = klassNode[klassIndex].value;
    var name = "klassId"; //建立的input的name
    createInput(klassId, name, klassNode);
};

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
    if (node !== null)
        node.length = 0;
}

//创建选项
function createOption(node, inners) {
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

    if (value === 0)
        node.options[0].setAttribute("selected", "true");
    for (var i = 0; i<node.length; i++ ) {
        if (Number(node.options[i].value) === value) {
            console.log('选中了');
            node.options[i].setAttribute("selected", "true");
            return;
        }
    }
}

//创建一个input按钮
function createInput(value, name, position) {
    var newInput = document.createElement("input");
    newInput.name = name;
    newInput.value = value;
    position.appendChild(newInput);
}

