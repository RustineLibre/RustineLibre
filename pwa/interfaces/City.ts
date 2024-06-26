import {City as Gouv} from './Gouv';
import {City as Nominatim} from './Nominatim';

export interface City {
  id: string | number;
  cityCode?: string;
  name: string;
  postcode?: string;
  formatted_name: string;
  lat: string | number;
  lon: string | number;
}

export const createCities = (
  citiesResponse: Gouv[] | Nominatim[],
  useNominatim: boolean
): City[] => {
  return useNominatim
    ? createCitiesWithNominatimAPI(citiesResponse as Nominatim[])
    : createCitiesWithGouvAPI(citiesResponse as Gouv[]);
};

export const createCitiesWithGouvAPI = (citiesResponse: Gouv[]): City[] => {
  let cities: City[] = [];

  citiesResponse.forEach((city: Gouv) => {
    const args = [
      city.nom_commune_complet,
      city.code_postal,
      city.longitude,
      city.latitude,
    ];

    if (!args.some((arg) => arg === undefined)) {
      cities.push({
        id: city.id,
        cityCode: city.code_commune_INSEE,
        name: city.nom_commune_complet,
        postcode: city.code_postal,
        formatted_name:
          city.nom_commune_complet + ', ' + city.code_postal + ', ' + 'France',
        lat: city.latitude,
        lon: city.longitude,
      });
    }
  });

  return cities;
};

export const createCitiesWithNominatimAPI = (
  citiesResponse: Nominatim[]
): City[] => {
  let cities: City[] = [];
  let citiesNames: string[] = [];

  citiesResponse.forEach((city: Nominatim) => {
    const args = [city.display_name, city.place_id, city.lat, city.lon];

    if (
      !args.some((arg) => arg === undefined) &&
      !citiesNames.includes(city.display_name)
    ) {
      citiesNames.push(city.display_name);
      cities.push({
        id: city.place_id,
        name: city.display_name,
        formatted_name: city.display_name,
        lat: city.lat,
        lon: city.lon,
      });
    }
  });

  return cities;
};
