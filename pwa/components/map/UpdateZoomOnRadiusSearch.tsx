import {useMap} from 'react-leaflet';
import {useEffect} from 'react';

interface UpdateZoomOnRadiusSearchProps {
  searchRadius: string;
}
const UpdateZoomOnRadiusSearch = ({
  searchRadius,
}: UpdateZoomOnRadiusSearchProps) => {
  const map = useMap();

  useEffect(() => {
    if (map) {
      switch (searchRadius) {
        case '5000':
          map.setZoom(12);
          break;
        case '10000':
        case '15000':
        case '20000':
        case '30000':
          map.setZoom(11);
          break;
        case '40000':
          map.setZoom(10);
          break;
      }
    }
  }, [map, searchRadius]);

  return null;
};

export default UpdateZoomOnRadiusSearch;
