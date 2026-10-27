import {repairerResource} from '@resources/repairerResource';

describe('Testing the URL built from a resource identifier', () => {
  test.each([
    ['/repairers/12', 'http://localhost/repairers/12'],
    ['/appointments/3?page=2', 'http://localhost/appointments/3?page=2'],
    ['/auth/refresh', 'http://localhost/auth/refresh'],
  ])('Should keep the API path %s on the same origin', (id, expected) => {
    expect(repairerResource.getUrl(id)).toBe(expected);
  });

  test('Should use the resource endpoint without identifier', () => {
    expect(repairerResource.getUrl()).toBe('http://localhost/repairers');
  });

  test.each([
    ['.attacker.example/repairers/1'],
    [':8443/repairers/1'],
    ['//attacker.example/repairers/1'],
    ['/\\attacker.example/repairers/1'],
    ['https://attacker.example/repairers/1'],
    ['repairers/1'],
    [''],
  ])('Should refuse the identifier %s', (id) => {
    expect(() => repairerResource.getUrl(id)).toThrow();
  });
});
