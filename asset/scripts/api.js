const API = new XMLHttpRequest();
const API_URL = "http://localhost/broker-website/asset/config/api.php";

function request(method, data, callback) {
    let url = API_URL;
    let params = Object.keys(data).map(k => encodeURIComponent(k) + '=' + encodeURIComponent(data[k])).join('&');
    let body = null;

    if (method.toUpperCase() === 'GET') {
        url += '?' + params;
    } else {
        body = params;
    }


    API.open(method, url, true);
    API.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    API.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            callback(JSON.parse(this.responseText));
        }
    };
    API.send(body);
}



const Auth = {
    loginAdmin: function (username, password) {
        request('GET', { action: 'login', username, password }, (resp) => {
            if (resp.error || resp.DATABASE_CONNECTION_ERROR) {
                showNotification(resp.error || resp.DATABASE_CONNECTION_ERROR, 'error');
            } else if (resp.issAuthenticated) {
                if (resp.role === 'admin') {
                    showNotification('Login Successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'http://localhost/broker-website/dashboard/admin/index.php';
                    }, 2000);
                } else {
                    showNotification('Invalid User Role', 'error');
                }

            } else {
                showNotification('Invalid username or password', 'error');
            }
        });
    },
    login: function (username, password) {
        request('GET', { action: 'login', username, password }, (resp) => {
            if (resp.error || resp.DATABASE_CONNECTION_ERROR) {
                showNotification(resp.error || resp.DATABASE_CONNECTION_ERROR, 'error');
            } else if (resp.issAuthenticated) {
                if (resp.role == 'user') {
                    showNotification('Login Successful! Redirecting...', 'success');
                    setTimeout(() => {
                        window.location.href = 'http://localhost/broker-website/dashboard/user/index.php';
                    }, 2000);
                } else {
                    showNotification('Invalid User Role', 'error');
                }

            } else {
                showNotification('Invalid username or password', 'error');
            }
        });
    },

    register: function (infodata = { username, fullname, password, confirmpassword, email, referrer_id }) {

        if (infodata.password == infodata.confirmpassword) {
            let data = {
                action: 'register',
                username: infodata.username,
                password: infodata.password,
                email: infodata.email,
                fullname: infodata.fullname,
                referrer_id: infodata.referrer_id
            }

            request('GET', data, (resp) => {
                if (resp.error || resp.DATABASE_CONNECTION_ERROR) {
                    showNotification(resp.error || resp.DATABASE_CONNECTION_ERROR, 'error');
                }
                if (resp.isRegistered) {
                    showNotification('Registration Successful! Redirecting to Login Page...', 'success');
                    setTimeout(() => {
                        window.location.href = 'http://localhost/broker-website/login.html';
                    }, 2000);
                } else if (resp.isRegistered === false && !resp.error) {
                    showNotification('Registration failed. Please try again.', 'error');
                }
            });
        } else {
            showNotification('Passwords do not match', 'error');
        }

    }
}