import sha256 from 'js-sha256';
import {
    str2bigInt,
    powMod,
    sub,
    add,
    mult,
    randBigInt,
    bigInt2str,
} from 'BigInt';

const srp = function() {
    this.n_base64 =
        'dadfccb918e5f651d7a1b851efab43f2c17068c69013e37033347e8da75ca8d8370c26c4fbf1a4aaa4afd9b5ab32343749ee4fbf6fa279856fd7c3ade30ecf2b';
    this.g = '2';
    this.hash_alg = 'sha256';
    this.k = this.hash(this.n_base64 + this.g);
    this.rand_length = 128;
};

srp.prototype.generateX = function(s, username, password) {
    var s = this.base2BigInt(s);
    s = this.bigIntToStr(s);
    const x = this.hash(s + this.hash(`${username}:${password}`));

    return x;
};

srp.prototype.generateV = function(x) {
    const g = str2bigInt(this.g, 10);
    const n = this.base2BigInt(this.n_base64);
    var x = this.base2BigInt(x);
    const v = this.bigIntToBase(powMod(g, x, n));

    return v;
};

srp.prototype.generateA = function(a) {
    const g = str2bigInt(this.g, 10);
    const n = this.base2BigInt(this.n_base64);
    var a = this.base2BigInt(a);

    const A = this.bigIntToBase(powMod(g, a, n));

    return A;
};

srp.prototype.generateClientS = function(A, B, a, x) {
    const u = this.base2BigInt(this.generateU(A, B));
    var B = this.base2BigInt(B);
    var a = this.base2BigInt(a);
    const k = this.base2BigInt(this.k);
    const g = str2bigInt(this.g, 10);
    const n = this.base2BigInt(this.n_base64);
    var x = this.base2BigInt(x);

    const S = this.bigIntToBase(
        powMod(sub(B, mult(k, powMod(g, x, n))), add(a, mult(u, x)), n),
    );

    return S;
};

srp.prototype.generateB = function(b, v) {
    const n = this.base2BigInt(this.n_base64);
    var v = this.base2BigInt(v);
    var b = this.base2BigInt(b);
    const k = this.base2BigInt(this.k);
    const g = str2bigInt(this.g, 10);

    const B = this.bigIntToBase(add(mult(k, v), powMod(g, b, n)));

    return B;
};

srp.prototype.generateServerS = function(A, B, b, v) {
    const u = this.base2BigInt(this.generateU(A, B));
    const n = this.base2BigInt(this.n_base64);
    var A = this.base2BigInt(A);
    var v = this.base2BigInt(v);
    var b = this.base2BigInt(b);

    const S = this.bigIntToBase(powMod(mult(A, powMod(v, u, n)), b, n));

    return S;
};

srp.prototype.getRandomSeed = function(length) {
    length = length || this.rand_length;

    return this.bigIntToBase(randBigInt(length * 4));
};

srp.prototype.generateU = function(A, B) {
    return this.hash(A + B);
};

srp.prototype.generateM1 = function(A, B, S) {
    return this.hash(A + B + S);
};

srp.prototype.generateM2 = function(A, M1, S) {
    return this.hash(A + M1 + S);
};

srp.prototype.generateK = function(S) {
    return this.hash(S);
};

srp.prototype.hash = function(value) {
    if (this.hash_alg === 'sha256') {
        return sha256(sha256(value));
    }

    throw 'hash algorithm not supported';
    return null;
};

srp.prototype.base2BigInt = function(value, base = 16) {
    return str2bigInt(value, base);
};

srp.prototype.bigIntToStr = function(value) {
    return bigInt2str(value, 10);
};

srp.prototype.bigIntToBase = function(value, base = 16) {
    return bigInt2str(value, base).toLowerCase();
};

export function createSrp() {
    return new srp();
}
