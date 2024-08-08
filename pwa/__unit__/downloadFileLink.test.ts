import {downloadFile} from '@utils/downloadFileLink';

describe('downloadFile', () => {
  let appendChildMock: jest.Mock;
  let removeChildMock: jest.Mock;

  beforeEach(() => {
    global.Response = jest.fn(() => ({
      blob: jest.fn().mockResolvedValue(new Blob()),
    })) as unknown as typeof Response;

    global.URL.createObjectURL = jest.fn(() => 'blob:mock-url');
    global.URL.revokeObjectURL = jest.fn();

    appendChildMock = jest.fn((child) => {
      // Simulates that the parent is set when adding
      child.parentNode = {removeChild: removeChildMock} as unknown as Node;
    });
    removeChildMock = jest.fn();

    global.document.createElement = jest.fn().mockImplementation(() => ({
      href: '',
      download: '',
      style: {},
      click: jest.fn(),
      setAttribute: jest.fn(),
      parentNode: null,
    }));

    jest
      .spyOn(document.body, 'appendChild')
      .mockImplementation(appendChildMock);
    jest
      .spyOn(document.body, 'removeChild')
      .mockImplementation(removeChildMock);
  });

  afterEach(() => {
    jest.restoreAllMocks();
  });

  test('creates a downloadable Blob and triggers download', async () => {
    const mockResponse = new global.Response();
    const filename = 'my_file';

    await downloadFile(mockResponse, filename);

    expect(mockResponse.blob).toHaveBeenCalledTimes(1);

    const createdBlob = await (mockResponse.blob as jest.Mock).mock.results[0]
      .value;
    expect(global.URL.createObjectURL).toHaveBeenCalledWith(createdBlob);
    expect(global.document.createElement).toHaveBeenCalledWith('a');

    const createdLink = (global.document.createElement as jest.Mock).mock
      .results[0].value;
    const now = new Date();
    const expectedFilename = `${filename}_${now
      .toLocaleDateString('fr-FR')
      .replace(/\//g, '-')}_${now
      .toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'})
      .replace(/:/g, '-')}.csv`;

    expect(createdLink.href).toBe('blob:mock-url');
    expect(createdLink.setAttribute).toHaveBeenCalledWith(
      'download',
      expectedFilename
    );

    expect(appendChildMock).toHaveBeenCalledWith(createdLink);
    expect(createdLink.click).toHaveBeenCalled();
    expect(removeChildMock).toHaveBeenCalledWith(createdLink);
    expect(global.URL.revokeObjectURL).toHaveBeenCalledWith('blob:mock-url');
  });
});
