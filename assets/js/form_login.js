import { createSrp } from './srp';
const srp = createSrp();
import axios from 'axios';

function generateSession(data) {
    let secondMatcher = srp.generateM2(data.A, data.matcher, data.S);
    if (secondMatcher !== data.secondMatcher) {
        alert('error, server is compromised');
        return;
    }

    let sessionKey = srp.generateK(data.S);

    axios.post(
        '/srp_login_confirm', {
            'clientSessionKey': sessionKey,
            'email': data.email
        })
        .then(function (response) {
            document.location.href = response.data.redirect;
        })
        .catch(function (error) {

        });
}

function generateMatcher(data) {
    let x = srp.generateX(data.seed, data.email, data.password);
    let S = srp.generateClientS(data.A, data.B, data.a, x);
    let M1 = srp.generateM1(data.A, data.B, S);

    data.S = S;
    data.matcher = M1;
    axios.post(
        '/api/auth/srpp/login2', {
            'matcher': M1,
            'email': data.email
        })
        .then(function (response) {
            data.secondMatcher = response.data.secondMatcher;

            generateSession(data);
        })
        .catch(function (error) {

        });
}

document.getElementById('login').onsubmit = function () {
    let email = this.elements.item(0).value;
    let password = this.elements.item(1).value;

    let a = srp.getRandomSeed();
    let A = srp.generateA(a);

    axios.post(
        this.action, {
            'email': this.elements.item(0).value,
            'publicEphemeralValue': A,
        })
        .then(function (response) {

            generateMatcher({
                'email': email,
                'password': password,
                'A': A,
                'a': a,
                'B': response.data.publicEphemeralValue,
                'seed': response.data.seed,
            });
        })
        .catch(function (error) {

        });

    return false;
}
