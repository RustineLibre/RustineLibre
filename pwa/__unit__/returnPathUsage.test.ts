import {readFileSync} from 'fs';
import {join} from 'path';

// Login and registration must both validate the "next" query parameter with the shared helper
describe('Testing that the authentication pages validate the return path', () => {
  test.each([['pages/login.tsx'], ['pages/inscription.tsx']])(
    'Should validate the return path in %s',
    (file) => {
      const source = readFileSync(join(__dirname, '..', file), 'utf8');

      expect(source).toContain("from '@helpers/returnPathHelper'");
      expect(source).toContain('getSafeReturnPath(router.query.next)');
      expect(source).not.toMatch(/router\.query\.next\.join/);
    }
  );
});
