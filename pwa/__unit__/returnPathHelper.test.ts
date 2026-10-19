import {getSafeReturnPath} from '@helpers/returnPathHelper';

describe('Testing the validation of the return path after authentication', () => {
  test.each([
    ['/', '/'],
    ['/velos/mes-velos', '/velos/mes-velos'],
    [
      '/reparateur/12/creneaux?date=2024-05-01#horaires',
      '/reparateur/12/creneaux?date=2024-05-01#horaires',
    ],
    [
      '/sradmin/boutiques/3/agenda?selectedDate=2024-05-01T10%3A00%3A00',
      '/sradmin/boutiques/3/agenda?selectedDate=2024-05-01T10%3A00%3A00',
    ],
    ['/recherche?q=v%C3%A9lo', '/recherche?q=v%C3%A9lo'],
  ])('Should keep the application path %s', (value, expected) => {
    expect(getSafeReturnPath(value)).toBe(expected);
  });

  test.each([
    ['javascript:alert(1)'],
    ['JavaScript:alert(1)'],
    [' javascript:alert(1)'],
    ['java\tscript:alert(1)'],
    ['data:text/html,<script>alert(1)</script>'],
    ['vbscript:msgbox(1)'],
    ['https://evil.example/login'],
    ['http://evil.example'],
    ['//evil.example'],
    ['///evil.example'],
    ['/\\evil.example'],
    ['\\\\evil.example'],
    ['\\/evil.example'],
    ['/\t/evil.example'],
    ['/\n/evil.example'],
    ['/%2F/evil.example'],
    ['/%5Cevil.example'],
    ['%2F%2Fevil.example'],
    ['/%00'],
    ['/%E0%A4%A'],
    ['velos/mes-velos'],
    [''],
  ])('Should reject %j and use the fallback', (value) => {
    expect(getSafeReturnPath(value)).toBe('/');
  });

  test('Should reject a missing value and an array of values', () => {
    expect(getSafeReturnPath(undefined)).toBe('/');
    expect(getSafeReturnPath(['/velos/mes-velos'])).toBe('/');
    expect(getSafeReturnPath(['/velos', '/mes-velos'])).toBe('/');
    expect(getSafeReturnPath(['javascript:', 'alert(1)'])).toBe('/');
  });

  test('Should use the given fallback path', () => {
    expect(getSafeReturnPath('javascript:alert(1)', '/mon-compte')).toBe(
      '/mon-compte'
    );
  });
});
